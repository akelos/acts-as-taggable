<?php

class TagList extends AkObject
{
    var $_tags = array();
    var $_cachedTagNames = array();
    var $_cachedSafeTagNames = array();
    var $_cacheId = null;
    var $_cachedTagString = '';
    var $_newTagNames = array();
    var $_removeTagNames = array();
    var $_separator = ',';
    function __construct()
    {
        $args = func_get_args();
        if(count($args)>0) {
            call_user_func_array(array(&$this,'addTags'),$args);
        }
    }
    function setSeparator($sep)
    {
        $this->_separator = $sep;
    }
    function _generateTagNames()
    {
        $tagHelper = new Tag();
        $safeTagNames = array();
        $tagNames = array();
        foreach ($this->_tags as $tag) {
            $name = $tag->name;
            $tagNames[] = $name;
        }
        foreach ($this->_newTagNames as $newTag) {
            //$name = $this->_sanitizeTagName($newTag);
            $tagNames[] = $newTag;
        }
        sort($tagNames);
        $tagNames = array_unique($tagNames);
        $tagNames = array_diff($tagNames, $this->_removeTagNames);
        $slugHelper = new ActsAsSluggable(&$tagHelper,array('slug_source'=>'name','slug_target'=>'slug'));
        $tagHelper->sluggable = &$slugHelper;
        foreach ($tagNames as $tname) {
            $tagHelper->name = $tname;
            $safeTagNames[]=$slugHelper->_generateSlug(&$tagHelper);
        }
        
        $this->_cachedTagNames = array_values($tagNames);
        $this->_cachedSafeTagNames = array_values($safeTagNames);
        $this->_cacheId = md5(implode(',',$tagNames));
    }
    function _generateCachedTagString()
    {
        $items = array();
        foreach ($this->_cachedTagNames as $tag) {
            $items[] = $this->_sanitizeTagName($tag);
        }
        $this->_cachedTagString = implode($this->_separator,$items);
    }
    function _generateCache()
    {
        $this->_generateTagNames();
        $this->_generateCachedTagString();
    }
    function toString()
    {
        if (null == $this->_cacheId) {
            $this->_generateCache();
        }
        return $this->_cachedTagString;

    }
    
    function _sanitizeTagName($tag) {
        if (strstr($tag,$this->_separator) && !preg_match('/^".*?"$/',$tag)) {
            $tag = '"'.str_replace('"','\"',$tag).'"';
        }
        return $tag;
    }

    function getTags()
    {
        //var_dump($this);
        if (null == $this->_cacheId) {
            $this->_generateCache();
        } 
        return $this->_cachedTagNames;
    }
    function getSafeTags()
    {
        if (null == $this->_cacheId) {
            $this->_generateCache();
        } 
        return $this->_cachedSafeTagNames;
    }
    function __toString()
    {
        return $this->toString();
    }
    function addTag($tag)
    {
        if (is_string($tag)) {
            $this->_newTagNames[] = $tag;
            $this->_decache();
            return true;
        } else if (is_object($tag) && is_a($tag,'Tag')) {
            if ($tag->isNewRecord() || ($tag->id<1 && !empty($tag->name))) {
                $this->_newTagNames[] = $tag->name;
                $this->_decache();
                return true;
            } else if ($tag->id>0 && !in_array($this->_sanitizeTagName($tag->name),$this->_cachedTagNames)) {
                $this->_tags[]=$tag;
                $this->_decache();
                return true;
            }
        }
        return false;
    }
    
    function removeTag($tag) {
        if (is_string($tag)) {
            if (in_array($tag,$this->_newTagNames)) {
                $this->_newTagNames = array_diff($this->_newTagNames,array($tag));
                $this->_decache();
                return true;
            } else if (!in_array($tag,$this->_removeTagNames)) {
                $this->_removeTagNames[] = $tag;
                $this->_decache();
                return true;
            }
        } else if (is_object($tag) && is_a($tag,'Tag')) {
            if ($tag->isNewRecord()) {
                $this->_newTagNames = array_diff($this->_newTagNames,array($tag->name));
                $this->_decache();
                return true;
            } else if ($tag->id>0 && !in_array($tag->name,$this->_removeTagNames)) {
                $this->_removeTagNames[] = $tag;
                $this->_decache();
                return true;
            }
        }
        return false;
    }
    function setTags($tags)
    {
        $args = func_get_args();
        if (count($args)>1) {
            $tags = $args;
        }
        $this->_generateTagNames();
        $tags = is_array($tags)? $tags: (is_string($tags)?$this->_parseTagString($tags):array());

        $removeTags = array_diff($this->_cachedTagNames, $tags);
        $this->_removeTagNames = $removeTags;
        $newTags = array_diff($tags,$this->_cachedTagNames);
        
        $this->_newTagNames = array_merge($this->_newTagNames,$newTags);
        $this->_decache();
    }
    function _decache()
    {
        $this->_cacheId=null;
        $this->_cachedTagNames=array();
        $this->_cachedTagString='';
    }
    function addTags()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                call_user_func_array(array(&$this,'addTags'),$arg);
            } else {
                $this->addTag($arg);
            }
        }
    }
    function removeTags()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                call_user_func_array(array(&$this,'removeTags'),$arg);
            } else {
                $this->removeTag($arg);
            }
        }
    }
    function getRemovedTags()
    {
        return $this->_removeTagNames;
    }
    function getNewTags()
    {
        return $this->_newTagNames;
    }
    function parseTagString($tagString)
    {
        return $this->_parseTagString($tagString);
    }
    function _parseTagString($tagString)
    {
        $tags = array();
        $replaceEscaped = md5(time());
        $oldLength = strlen($tagString);
        $tagString = str_replace('\"',$replaceEscaped,$tagString);
        $reEscape = false;
        if (strlen($tagString)!=$oldLength) {
            $reEscape = true;
        }
        preg_match_all('/"([^"]+)"/',$tagString, $matches);
        if (isset($matches[0]) && count($matches[0])>0) {
            $tagString = str_replace($matches[0],'',$tagString);
            $tags = array_merge($tags,$matches[1]);
        }
        $restTags = split($this->_separator,$tagString);
        $tags = array_merge($tags,$restTags);
        $tags = array_unique($tags);
        $tags = array_diff($tags,array(''));


        $newTags = array();
        foreach ($tags as $idx=>$tag) {
            $tag = trim($tag);
            if ($reEscape) {
                $tag = str_replace($replaceEscaped,'\"',$tag);
            }
            $newTags[] = $tag;
        }
        $tags = $newTags;

        return $tags;
    }
    
    function size()
    {
        return count($this->getTags());
    }
    
    function reset()
    {
        $this->_tags = array();
        $this->_newTagNames = array();
        $this->_removeTagNames = array();
        $this->_cachedTagNames = array();
        $this->_cachedTagString = '';
        $this->_cacheId = null;
    }
}
?>