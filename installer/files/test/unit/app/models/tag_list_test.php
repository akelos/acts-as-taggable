<?php
require_once(AK_APP_DIR.DS.'models'.DS.'tag_list.php');
require_once(AK_APP_DIR.DS.'models'.DS.'tag.php');
class TagListTest extends AkUnitTest
{
    var $tagList;
    function setUp()
    {
        $this->tagList = new TagList();
        $this->uninstallAndInstallMigration('ActsAsTaggablePlugin');
    }
    
    function test_construct_multiple_parameters()
    {
        $tagList = new TagList('tag1','tag2','tag3');
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
    }
    
    function test_construct_array()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
    }
    
    function test_construct_array_string_mixed()
    {
        $tagList = new TagList(array('tag1','tag2'),'tag3');
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
    }
    
    function test_construct_tag_objects()
    {
        $tag = new Tag();
        $tag->name = 'tag1';
        $tagList = new TagList(array($tag,'tag2'),'tag3');
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
    }
    
    function test_construct_duplicates()
    {
        $tagList = new TagList(array('tag1','tag2','tag2','tag1'),'tag3','tag1');
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
    }
    function test_add_tag()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->addTag('tag4');
        $this->assertEqual(array('tag1','tag2','tag3','tag4'),$tagList->getTags());
    }
    
    function test_add_tag_duplicates()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->addTag('tag3');
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
    }
    
    function test_add_tags()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->addTags('tag4','tag5');
        $this->assertEqual(array('tag1','tag2','tag3','tag4','tag5'),$tagList->getTags());
    }
    
    function test_add_tags_duplicates()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->addTags('tag4','tag5','tag1','tag2');
        $this->assertEqual(array('tag1','tag2','tag3','tag4','tag5'),$tagList->getTags());
    }
    
    function test_set_tags_multi_params()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->setTags('tag4','tag5');
        $this->assertEqual(array('tag4','tag5'),$tagList->getTags());
    }
    
    function test_set_tags_array()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->setTags(array('tag4','tag5'));
        $this->assertEqual(array('tag4','tag5'),$tagList->getTags());
    }
    
    function test_set_tags_string()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->setTags('tag4,tag5');
        $this->assertEqual(array('tag4','tag5'),$tagList->getTags());
    }
    function test_set_tags_string_quoted()
    {
        $tagList = new TagList();
        
        $tagList->setTags('"tag 4",tag5,"tag 6"');
        $this->assertEqual(array('tag 4','tag 6','tag5'),$tagList->getTags());
    }
    function test_remove_tags_multi_params()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->removeTags('tag1','tag2');
        $this->assertEqual(array('tag3'),$tagList->getTags());
    }
    
    function test_remove_tags_array()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual(array('tag1','tag2','tag3'),$tagList->getTags());
        
        $tagList->removeTags(array('tag1','tag2'));
        $this->assertEqual(array('tag3'),$tagList->getTags());
    }
    
    function test_to_string()
    {
        $tagList = new TagList(array('tag1','tag2','tag3'));
        $this->assertEqual('tag1,tag2,tag3',$tagList->toString());
    }
    
    function test_to_string_quoted()
    {
        $tagList = new TagList();
        $tagList->setTags('tag 1,tag2');
        $this->assertEqual('tag 1,tag2',$tagList->toString());
    }
    function test_separator()
    {
        $tagList = new TagList();
        $tagList->setTags('tag 1,tag2');
        $this->assertEqual('tag 1,tag2',$tagList->toString());
        $tagList->_decache();
        $tagList->setSeparator('-');
        $this->assertEqual('tag 1-tag2',$tagList->toString());
        $tagList->_decache();
        $tagList->setSeparator(' ');
        $this->assertEqual('"tag 1" tag2',$tagList->toString());
        
        $tagList->_decache();
        $tagList->setSeparator('   ');
        $this->assertEqual('tag 1   tag2',$tagList->toString());
    }
}
?>