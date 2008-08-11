<?php

class Tagging extends ActiveRecord
{
    var $belongsTo = array('tag');
    
    function beforeDestroy()
    {
        $this->tag->load();
        $sql='UPDATE tag_cloud, tags SET tag_cloud.counter=tag_cloud.counter-1 
              WHERE tag_cloud.tag = tags.name AND tags.id = '.
              $this->tag_id.
              ' AND tag_cloud.taggable_type = '.
              $this->_db->quote_string($this->taggable_type);
        return $this->_db->execute($sql);
    }
    function incrementCounter()
    {
        $sql = 'SELECT id from tag_cloud WHERE tag = '.$this->_db->quote_string($this->tag->name).
               ' AND '.
               ' taggable_type = '.
               $this->_db->quote_string($this->taggable_type);
        $id = $this->_db->selectValue($sql);
        
        if ($id>=1) {
            $sql='UPDATE tag_cloud SET counter=counter+1 WHERE id = '. $id;
            $res = $this->_db->execute($sql);
        } else {
            $sql='INSERT INTO tag_cloud (tag,counter,taggable_type) VALUES ('.
            $this->_db->quote_string($this->tag->name).','.
            '1,'.
            $this->_db->quote_string($this->taggable_type).')';
            $res = $this->_db->execute($sql);
        }
        return $res;
    }

    function afterCreate()
    {
        return $this->incrementCounter();
    }
    
}
?>