<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2007, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+
/**
 see http://svn.viney.net.nz/things/rails/plugins/acts_as_taggable_on_steroids/lib/acts_as_taggable.rb
*/
/**
* @package ActiveRecord
* @subpackage Behaviours
* @author Arno Schneider <arno a.t. bermilabs dot com>
* @copyright Copyright (c) 2002-2007, Akelos Media, S.L. http://www.akelos.org
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
*/

require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.'AkObserver.php');
require_once(AK_APP_DIR.DS.'models'.DS.'tag.php');
require_once(AK_APP_DIR.DS.'models'.DS.'tagging.php');
require_once(AK_APP_DIR.DS.'models'.DS.'tag_list.php');
class ActsAsTaggable extends AkObserver
{
    var $_instance;
    var $_taggableType;
    var $_tagList;
    var $_loaded = false;
    var $_cached_tag_column;

    function ActsAsTaggable(&$ActiveRecordInstance, $options = array())
    {
        $this->_instance = &$ActiveRecordInstance;
        $this->_tagList = new TagList();
        $this->observe(&$this->_instance);
        $this->init($options);
    }
    
    function init($options = array())
    {
        $default_options = array('separator'=>',','cache_column'=>false);
        
        $options = array_merge($default_options, $options);
        if (isset($options['cache_column'])) {
            $this->_cached_tag_column = $options['cache_column'];
        }
        if ($options['separator']!=false) {
            $this->_tagList->setSeparator($options['separator']);
        }
    }
    
    function load()
    {
        $this->_loadTags();
    }
    function getTagType()
    {
        return $this->_instance->getTagType();
    }
    function beforeDestroy(&$record)
    {
        /**
         * delete the taggins entries for this taggable_id and taggable_type == CLASS
         */    
        $TA = new Tagging();
        $TA->deleteAll('taggable_id = '.$record->id.' AND taggable_type = ' . $record->_db->quote_string($record->getTagType()));
        return true;
    }
    
    function beforeSave(&$record)
    {
        if (isset($this->_cached_tag_column)) {
            $tagList = $record->getTagList();
            $record->{$this->_cached_tag_column} = $tagList->toString();
        }
        return true;
    }
    function afterSave(&$record)
    {
        return $this->_saveTags(&$record);
    }

    function _saveTags(&$record)
    {

        $tagList = &$record->getTagList();
        if ($tagList->size()==0) {
            return true;
        }
        
        $removedTags = $tagList->getRemovedTags();
        $tagNames = $tagList->getTags();
        $newTags = $tagList->getNewTags();
        $removedTags = array_merge($removedTags,$tagNames);
        $removedTags = array_unique($removedTags);
        $removedTags = array_diff($removedTags,$newTags);
        if (count($removedTags)>0) {
            $quotedRemovedTags = array();
            foreach($removedTags as $rm) {
                $quotedRemovedTags[] = $record->_db->quote_string($rm);
            }
            $sql='UPDATE tag_cloud SET counter = counter-1 WHERE taggable_type='.
                 $record->_db->quote_string($record->getTagType()).
                 ' AND tag IN ('.implode(',',$quotedRemovedTags).')';
            $record->_db->execute($sql);
        }
        $newTags = $tagList->getNewTags();
        $TA = new Tagging();
        $TG = new Tag();
        $TA->deleteAll('taggable_id = '.$record->id.' AND taggable_type = ' . $record->_db->quote_string($record->getTagType()));
        
        $savedTags = array();
        foreach ($tagNames as $tagName) {
            $tag=&$TG->findOrCreateBy('name',$tagName);
            $tagging = $TA->findFirstBy('tag_id AND taggable_id AND taggable_type',$tag->id,$record->id,$record->getTagType());
            if (!$tagging) {
                $tagging = new Tagging();
                $attributes = array();
                $attributes['tag_id'] = $tag->id;
                $attributes['taggable_id'] = $record->id;
                $attributes['taggable_type'] = $record->getTagType();
                $tagging->setAttributes($attributes,true);
                $tagging->tag = &$tag;
                $res = $tagging->save();
            }
            //s$Tagging->findOrCreateBy('tag_id AND taggable_id AND taggable_type',$tag->id,$record->id,$this->_taggableType);
            $savedTags[] = &$tag;
        }
        $tagList->reset();
        $tagList->addTags($savedTags);
        return true;
    }
    function setTags()
    {
        $args = func_get_args();
        call_user_func_array(array(&$this->_tagList,'setTags'),$args);
    }
    function &getTagList()
    {
        return $this->_tagList;
    }
    function _loadTags()
    {
        
        if ($this->_instance->isNewRecord()) {
            return;
        }

        $tagList = &$this->getTagList();
        $tagList->reset();
        $sql='SELECT tags.* FROM taggings LEFT JOIN tags ON tags.id = taggings.tag_id WHERE taggings.taggable_id='.$this->_instance->id.' AND taggings.taggable_type = "'.$this->_instance->getTagType().'"';
        $Tag = new Tag();
        $tags =  &$Tag->findBySql($sql);
        $this->addTags($tags);
    }
    
    
    
    function getTags()
    {
        return $this->_tagList->getTags();
    }
    function getSafeTags()
    {
        return $this->_tagList->getSafeTags();
    }
    function &findRelatedTags($tags, $options = array())
    {
        $tagList = new TagList();
        $tags = is_array($tags)? $tags: $this->_tagList->parseTagString($tags);
        $related_models = &$this->findTaggedWith($tags);
        if(empty($related_models)) return $tagList;
        $ids = array();
        foreach ($related_models as $rm) {
            $ids[]=$rm->id;
        }
        $related_ids = implode(',',$ids);
        
        $tagMap = array();
        foreach ($tags as $t) {
            $tagMap[] = $this->_instance->_db->quote_string($t);
        }
        
        $tag = new Tag();
        $tagging = new Tagging();
        $tagTableName = $tag->getTableName();
        $taggingTableName = $tagging->getTableName();
        $taggableType = $this->getTagType();
        $related_tags = &$tag->find('all',array('select'=>"{$tagTableName}.*",
                                   'joins'=>"JOIN {$taggingTableName} ON {$taggingTableName}.taggable_type = '{$taggableType}'",
                                   'order'=> isset($options['order'])? $options['order']:"COUNT({$tagTableName}.id) DESC, {$tagTableName}.name",
                                   'group'=>"{$tagTableName}.id, {$tagTableName}.name HAVING {$tagTableName}.name NOT IN (".implode(',',$tagMap).")"));
        
        $tagList->addTags($related_tags);
        $tagList->removeTags($tags);
        return $tagList;
       
    }
    
    function &findTaggedWith($tags, $options = array())
    {
        $options = $this->_findOptionsForFindTaggedWith($tags,$options);
        return $this->_instance->find('all',$options);
    }
    

    
    function _findOptionsForFindTaggedWith($tags, $options = array())
    {
        $tags = is_array($tags)?$tags:$this->_tagList->parseTagString($tags);
        if (empty($tags)) return array();
        $tags = array_unique($tags);
        $conditions = array();
        
        $taggableType = $this->_instance->getTagType();
        $taggings_alias = $taggableType.'_taggings';
        $tags_alias = $taggableType.'_tags';
        $tag = new Tag();
        $tagging = new Tagging();
        $tagging_table_name=$tagging->getTableName();
        $table_name = $this->_instance->getTableName();
        $table_pk = $this->_instance->getPrimaryKey();
        $tag_table_name=$tag->getTableName();
        $tag_conditions = $this->_tagsCondition($tags,$tags_alias);
        $tags_size = count($tags);
        if (isset($options['exclude'])) {
            unset($options['exclude']);
            $conditions[]=<<<END
            $table_name.id NOT IN
                (SELECT $tagging_table_name.taggable_id FROM $tagging_table_name
                 INNER JOIN $tag_table_name ON $tagging_table_name.tag_id = $tag_table_name.id
                 WHERE $tag_conditions) AND $taggings_alias.taggable_type = '$taggableType'
END;
        } else if (isset($options['match_all'])) {
            unset($options['match_all']);
            $conditions[]=<<<END
            (SELECT COUNT(*) FROM $tagging_table_name
                 INNER JOIN $tag_table_name ON $tagging_table_name.tag_id = $tag_table_name.id
                 WHERE $tagging_table_name.taggable_type = '$taggableType' AND
                 taggable_id = $table_name.id AND
                 $tag_conditions) = $tags_size
END;

        } else {
            $conditions[] = $tag_conditions;
        }
        
        $default_options = array('select'=>"DISTINCT $table_name.*",
                         'joins'=>"INNER JOIN $tagging_table_name $taggings_alias ON $taggings_alias.taggable_id = $table_name.$table_pk AND $taggings_alias.taggable_type = '$taggableType' ".
                                  "INNER JOIN $tag_table_name $tags_alias ON $tags_alias.id = $taggings_alias.tag_id",
                         'conditions'=>implode(' AND ',$conditions));
        $options = array_merge($default_options, $options);
        return $options;
        
    }
    
    function addTag($tag)
    {
        return $this->_tagList->addTag($tag);
    }
    function removeTag($tag)
    {
        return $this->_tagList->removeTag($tag);
    }
    function addTags()
    {
        $args = func_get_args();
        return call_user_func_array(array(&$this->_tagList,'addTags'), $args);
    }
    function removeTags()
    {
        $args = func_get_args();
        return call_user_func_array(array(&$this->_tagList,'removeTags'), $args);
    }
    
    function _tagsCondition($tags = array(), $table_name = null)
    {
        if ($table_name == null) {
            $tag = new Tag();
            $table_name = $tag->getTableName();
        }
        $condition = '';
        $comparison = array();
        foreach ($tags as $tag) {
            $comparison[] = $table_name.'.name LIKE '.$this->_instance->_db->quote_string($tag);
            $comparison[] = $table_name.'.slug LIKE '.$this->_instance->_db->quote_string($tag);
        }
        $condition = implode(' OR ', $comparison);
        return $condition;
    }
}
?>