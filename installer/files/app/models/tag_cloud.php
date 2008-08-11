<?php

class TagCloud extends AkObject
{
    var $_type;
    var $_db;
    
    function __construct($type = null)
    {
        if (is_object($type) && method_exists($type,'getTagType')) {
            $type = $type->getTagType();
        } else if (is_string($type)) {
            $type = AkInflector::tableize($type);
        }
        $type = strtolower($type);
        $this->_type = $type;
        $this->_db = &Ak::db();
    }
    
    function get()
    {
        if (!isset($this->_cache)) {
            $sql = 'SELECT tag,slug,counter FROM tag_cloud LEFT JOIN tags on tag_cloud.tag=tags.name WHERE counter>0 ';
            if ($this->_type!=null) {
                $sql.= ' AND taggable_type='.$this->_db->quote_string($this->_type);
            }
            $sql.=' ORDER BY tag';
            $this->_cache = $this->_db->selectAll($sql);
        }
        
        return $this->_cache;
    }
    
    function getTotalCount()
    {
        if (!isset($this->_totalCount)) {
            $arr = $this->get();
            $this->_totalCount = 0;
            foreach($arr as $t) {
                $this->_totalCount+=$t['counter'];
            }
        }
        return $this->_totalCount;
    }
    
    function reset()
    {
        unset($this->_cache);
        unset($this->_totalCount);
    }
    
}
?>