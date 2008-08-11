<?php

class ActsAsTaggablePluginInstaller extends AkInstaller
{
    function down_1()
    {
        $this->dropTable('tags');
        $this->dropTable('taggings');
        $this->dropTable('tag_cloud');
    }
    
     function up_1()
    {
        $this->createTable('tags','id,name,slug');
        $this->createTable('taggings','id,tag_id,taggable_id,taggable_type string(128)');
        //$this->addIndex('taggings','tag_id');
        $this->addIndex('taggings','taggable_id,taggable_type','idx_taggable_id_type');
        $this->createTable('tag_cloud','id,tag,counter integer,taggable_type string(128)');
        $this->addIndex('tag_cloud','UNIQUE taggable_type,tag','unq_taggable_type_tag');
    }
}
?>