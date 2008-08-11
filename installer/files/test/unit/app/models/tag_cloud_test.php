<?php
require_once(AK_APP_DIR.DS.'models'.DS.'tag_list.php');
require_once(AK_APP_DIR.DS.'models'.DS.'taggable_thing.php');
require_once(AK_APP_DIR.DS.'models'.DS.'tag.php');
require_once(AK_APP_DIR.DS.'models'.DS.'tag_cloud.php');
class TagCloudTest extends AkUnitTest
{
    var $tagList;
    function setUp()
    {
        $this->uninstallAndInstallMigration('ActsAsTaggablePlugin');
    }
    
    function test_person_set_tags_and_save_get_tag_cloud()
    {
        $TaggableThing = new TaggableThing();
        $TaggableThing->name='test2';
        $TaggableThing->setTags('test3,test2');
        $this->assertEqual(array('test2','test3'),$TaggableThing->getTags());
        $TaggableThing->save();

        $tagCloud = new TagCloud($TaggableThing);
        $tagCloudArr = $tagCloud->get();
        $expectedTagCloud1 = array(array('tag'=>'test2','counter'=>1,'slug'=>'test2'),
                                   array('tag'=>'test3','counter'=>1,'slug'=>'test3'));
        $this->assertEqual($expectedTagCloud1,$tagCloudArr);
        
        $TaggableThing = new TaggableThing();
        $TaggableThing->name='test3';
        $TaggableThing->setTags('test3,test1');
        $this->assertEqual(array('test1','test3'),$TaggableThing->getTags());
        $TaggableThing->save();
        $this->assertEqual(array('test1','test3'),$TaggableThing->getTags());
        $expectedTagCloud2 = array(array('tag'=>'test1','counter'=>1,'slug'=>'test1'),
                                   array('tag'=>'test2','counter'=>1,'slug'=>'test2'),
                                   array('tag'=>'test3','counter'=>2,'slug'=>'test3'));
        $tagCloud->reset();
        $newTagCloud = $tagCloud->get();
        $this->assertEqual($expectedTagCloud2,$newTagCloud);
    }
    
    function test_person_set_tags_and_save_remove_get_tag_cloud()
    {
        $TaggableThing = new TaggableThing();
        $TaggableThing->name='test2';
        $TaggableThing->setTags('test3,test2');
        $this->assertEqual(array('test2','test3'),$TaggableThing->getTags());
        $TaggableThing->save();
        
        $tagCloud = new TagCloud($TaggableThing);
        $tagCloudArr = $tagCloud->get();
        $expectedTagCloud1 = array(array('tag'=>'test2','counter'=>1,'slug'=>'test2'),
                                   array('tag'=>'test3','counter'=>1,'slug'=>'test3'));
        
        $taggedItems = &$TaggableThing->findTaggedWith('test2');
        $this->assertEqual(1,count($taggedItems));
        $this->assertEqual('test2',$taggedItems[0]->name);
        $this->assertEqual(array('test2','test3'),$taggedItems[0]->getTags());
        $taggedItems[0]->removeTag('test3');
        $this->assertEqual(array('test2'),$taggedItems[0]->getTags());
        $expectedTagCloud2 = array(array('tag'=>'test2','counter'=>1,'slug'=>'test2'));
        $taggedItems[0]->save();
        
        $tagCloud->reset();
        $this->assertEqual($expectedTagCloud2,$tagCloud->get());
        
    }
    
    function test_get_total_count()
    {
        $TaggableThing = new TaggableThing();
        $TaggableThing->name='test2';
        $TaggableThing->setTags('test3,test2');
        $this->assertEqual(array('test2','test3'),$TaggableThing->getTags());
        $TaggableThing->save();
        
        
        $TaggableThing = new TaggableThing();
        $TaggableThing->name='test2';
        $TaggableThing->setTags('test3,test2,test1');
        $this->assertEqual(array('test1','test2','test3'),$TaggableThing->getTags());
        $TaggableThing->save();
        
        $tagCloud = new TagCloud($TaggableThing);
        $expectedCount = 5;
        $this->assertEqual($expectedCount,$tagCloud->getTotalCount());
    }
    
}
?>