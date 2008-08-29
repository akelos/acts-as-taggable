<?php
/**
 * @ExtensionPoint BaseActiveRecord
 *
 */
class ActsAsTaggableExtensions
{
    function findRelatedTags($tags, $options = array()) {
        if (isset($this->taggable) && method_exists($this->taggable,"findRelatedTags")) {
            return $this->taggable->findRelatedTags($tags, $options);
        }
        return false;
    }
    
function &findTaggedWith($args, $options = array()) {
        $false = false;
        if (isset($this->taggable) && method_exists($this->taggable,"findTaggedWith")) {
            return $this->taggable->findTaggedWith($args, $options);
        }
        return $false;
    }
    
function set_tags() {
        $args = func_get_args();
        if (isset($this->taggable) && method_exists($this->taggable,"setTags")) {
            return call_user_func_array(array(&$this->taggable,"setTags"),$args);
        }
        return false;
    }
    
function get_tags() {
        if (isset($this->taggable) && method_exists($this->taggable,"getTags")) {
            return $this->taggable->getTags();
        }
        return false;
    }
    
function get_safe_tags() {
        if (isset($this->taggable) && method_exists($this->taggable,"getSafeTags")) {
            return $this->taggable->getSafeTags();
        }
        return false;
    }
    
function addTag($tag) {
        if (isset($this->taggable) && method_exists($this->taggable,"addTag")) {
            return $this->taggable->addTag($tag);
        }
        return false;
    }
    
function addTags() {
        $args = func_get_args();
        if (isset($this->taggable) && method_exists($this->taggable,"addTags")) {
            return call_user_func_array(array(&$this->taggable,"addTags"),$args);
        }
        return false;
    }
    
function removeTag($tag) {
        if (isset($this->taggable) && method_exists($this->taggable,"removeTag")) {
            return $this->taggable->removeTag($tag);
        }
        return false;
    }
    
function removeTags($tag) {
        $args = func_get_args();
        if (isset($this->taggable) && method_exists($this->taggable,"removeTags")) {
            return call_user_func_array(array(&$this->taggable,"removeTags"),$args);
        }
        return false;
    }
    
function &get_tag_list() {
        $false = false;
        if (isset($this->taggable) && method_exists($this->taggable,"getTagList")) {
            $tagList = &$this->taggable->getTagList();
            return $tagList;
        }
        return $false;
    }
    
function &xinstantiate($record, $set_as_new = true) {
        $object = &parent::instantiate($record, $set_as_new);
        if (isset($object->taggable) && method_exists($object->taggable,"load")) {
            $object->taggable->load();
        }
        return $object;
    }
    
function get_tag_type() {
        if (!isset($this->__taggable_type)) {
            $this->__taggable_type = strtolower($this->getTableName());
        }
        
        return $this->__taggable_type;
    }
}
?>