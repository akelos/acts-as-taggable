<?php

class TaggableThing extends ActiveRecord
{
    var $acts_as = array('taggable'=>array('cache_column'=>'cache_tags'));
}
?>