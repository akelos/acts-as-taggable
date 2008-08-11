<?php
class TaggableThingInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('taggable_things','id,name,cache_tags');
    }
    
    function down_1()
    {
        $this->dropTable('taggable_things');
    }
}