<?php

class Tag extends ActiveRecord
{
    var $hasMany = array('taggings'=>array('dependent' => 'destroy'));
    var $acts_as = array('sluggable'=>array('slug_source'=>'name','slug_target'=>'slug'));
    
    function validate()
    {
        //$this->validatesPresenceOf('name');
        //$this->validatesUniquenessOf('name');
    }
    
    function beforeDestroy()
    {
        $sql='DELETE FROM tag_cloud WHERE tag = '.
        $this->_db->quote_string($this->name);
        return $this->_db->execute($sql);
    }
    
    function toString()
    {
        return @$this->__toString();
    }
    
    function __toString()
    {
        return @$this->name;
    }
    
    function equals(&$obj)
    {
        return (is_a($obj,'Tag')?$obj->name==$this->name:false);
    }
}
?>