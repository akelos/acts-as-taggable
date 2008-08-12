<?php
require_once(AK_BASE_DIR.DS.'app'.DS.'vendor'.DS.'plugins'.DS.'acts_as_taggable'.DS.'lib'.DS.'ActsAsTaggable.php');

class ActsAsTaggableTest extends AkUnitTest
{

    function setUp()
    {
        $this->uninstallAndInstallMigration('ActsAsTaggablePlugin');
        $this->installAndIncludeModels('TaggableThing');
        $this->taggable = new ActsAsTaggable(&$this->TaggableThing);
    }
    function _installTestData()
    {
        
    }
    function test_set_tags_array()
    {
        $this->taggable->setTags(array('tag1','tag2','tag3','tag4','tag5'));
        $this->assertEqual(array('tag1','tag2','tag3','tag4','tag5'), $this->taggable->getTags());
    }
    function test_set_tags_comma_separated()
    {
        $this->taggable->setTags('tag1,tag2,tag3,tag4,tag5');
        $this->assertEqual(array('tag1','tag2','tag3','tag4','tag5'), $this->taggable->getTags());
        
        $this->taggable->setTags('tag1, tag2  , tag3 ,  tag4, tag5     , tag6');
        $this->assertEqual(array('tag1','tag2','tag3','tag4','tag5','tag6'), $this->taggable->getTags());
    }
    
    function test_set_tags_space_separated()
    {
        $this->taggable->init(array('separator'=>' '));
        $this->taggable->setTags('tag1 tag2 tag3 tag4 tag5');
        $this->assertEqual(array('tag1','tag2','tag3','tag4','tag5'), $this->taggable->getTags());
        
        $this->taggable->setTags('tag1  tag2    tag3    tag4  tag5     tag6');
        $this->assertEqual(array('tag1','tag2','tag3','tag4','tag5','tag6'), $this->taggable->getTags());
    }
    
    
    function test_set_tags_quoted()
    {
        $this->taggable->init(array('separator'=>' '));
        $this->taggable->setTags('"tag 1" tag2 "tag 3" tag4 tag5');
        $this->assertEqual(array('tag 1','tag 3','tag2','tag4','tag5'), $this->taggable->getTags());
        
    }
    
    function test_set_tags_quoted_escaped()
    {
        $this->taggable->init(array('separator'=>' '));
        $this->taggable->setTags('"tag \"1" tag2 "tag \"3" tag4 tag5');
        $this->assertEqual(array('tag \"1','tag \"3','tag2','tag4','tag5'), $this->taggable->getTags());
        
    }
    
    function test_person_set_tags()
    {
        $this->TaggableThing->set_tags('test1,test2');
        $this->assertEqual(array('test1','test2'),$this->TaggableThing->get_tags());
    }
    
    function test_person_set_tags_and_save()
    {
        $this->TaggableThing->name='test1';
        $this->TaggableThing->set_tags('test1,test2');
        $this->assertEqual(array('test1','test2'),$this->TaggableThing->get_tags());
        $this->TaggableThing->save();
        $this->assertEqual(array('test1','test2'),$this->TaggableThing->get_tags());
    }
    
    function test_person_set_tags_and_save_url_slug1()
    {
        $taggableThing = new TaggableThing();
        $taggableThing->name='test1';
        $taggableThing->set_tags('España,test2');
        $this->assertEqual(array('España','test2'),$taggableThing->get_tags());
        $taggableThing->save();
        $this->assertEqual(array('España','test2'),$taggableThing->get_tags());
        $tagCloud = new TagCloud('TaggableThing');
        $arr = $tagCloud->get();
        $expected = array(array('tag'=>'España','counter'=>1,'slug'=>'espanya'),
                          array('tag'=>'test2','counter'=>1,'slug'=>'test2'));
        $this->assertEqual($expected, $arr);
    }
    
    function test_person_set_tags_and_save_url_slug2()
    {
        $this->TaggableThing->name='test1';
        $this->TaggableThing->set_tags('München,test2');
        $this->assertEqual(array('München','test2'),$this->TaggableThing->get_tags());
        $this->TaggableThing->save();
        $this->assertEqual(array('München','test2'),$this->TaggableThing->get_tags());
        $tagCloud = new TagCloud('TaggableThing');
        $arr = $tagCloud->get();
        $expected = array(array('tag'=>'München','counter'=>1,'slug'=>'muenchen'),
                          array('tag'=>'test2','counter'=>1,'slug'=>'test2'));
        $this->assertEqual($expected, $arr);
    }
    function test_person_set_tags_and_save_url_slug3()
    {
        $this->TaggableThing->name='test1';
        $this->TaggableThing->set_tags('someone@example.com,test2');
        $this->assertEqual(array('someone@example.com','test2'),$this->TaggableThing->get_tags());
        $this->TaggableThing->save();
        $this->assertEqual(array('someone@example.com','test2'),$this->TaggableThing->get_tags());
        $tagCloud = new TagCloud('TaggableThing');
        $arr = $tagCloud->get();
        $expected = array(array('tag'=>'someone@example.com','counter'=>1,'slug'=>'someone-at-example.com'),
                          array('tag'=>'test2','counter'=>1,'slug'=>'test2'));
        $this->assertEqual($expected, $arr);
    }
    function test_person_set_cache_tag_column()
    {
        $this->TaggableThing->name='test1';
        $this->TaggableThing->set_tags('test1,test2');
        $this->assertEqual(array('test1','test2'),$this->TaggableThing->get_tags());
        $this->TaggableThing->save();
        $this->assertEqual(array('test1','test2'),$this->TaggableThing->get_tags());
        $this->assertEqual('test1,test2',$this->TaggableThing->cache_tags);
    }
    function test_person_set_tags_and_save_find_with_tags()
    {
        $this->TaggableThing->name='test2';
        $this->TaggableThing->set_tags('test3,test2');
        $this->assertEqual(array('test2','test3'),$this->TaggableThing->get_tags());
        $this->TaggableThing->save();
        $taggableThings = $this->TaggableThing->findTaggedWith('test2');
        
        $this->assertEqual('test2',$taggableThings[0]->name);
        $this->assertEqual(array('test2','test3'),$taggableThings[0]->get_tags());
        
        $taggableThings = $this->TaggableThing->findTaggedWith('test3,test4');
        $this->assertEqual('test2',$taggableThings[0]->name);
        $this->assertEqual(array('test2','test3'),$taggableThings[0]->get_tags());
        
        $taggableThings = $this->TaggableThing->findTaggedWith('test2,test3',array('match_all'=>true));
        $this->assertEqual('test2',$taggableThings[0]->name);
        
        $taggableThings = $this->TaggableThing->findTaggedWith('test2,test3,test4',array('match_all'=>true));
        $this->assertFalse($taggableThings);
        
        $taggableThings = $this->TaggableThing->findTaggedWith('test2,test3',array('exclude'=>true));
        $this->assertFalse($taggableThings);
    }
    
    function test_find_related_tags()
    {
        $this->TaggableThing->name='test2';
        $this->TaggableThing->set_tags('test3,test2,test1');
        $this->TaggableThing->save();
        
        $relatedTagList = $this->TaggableThing->findRelatedTags('test1');
        $expected = array('test2','test3');
        $this->assertEqual($expected, $relatedTagList->getTags());
        
        $relatedTagList = $this->TaggableThing->findRelatedTags('test2');
        $expected = array('test1','test3');
        $this->assertEqual($expected, $relatedTagList->getTags());
    }
    
    function test_destroy_tag()
    {
        $tagCloud = new TagCloud('TaggableThing');
        $this->TaggableThing->name='test2';
        $this->TaggableThing->set_tags('test3,test2,test1');
        $this->TaggableThing->save();
        
        $tagCloudArr = $tagCloud->get();
        $this->assertEqual(array(array('tag'=>'test1','counter'=>1,'slug'=>'test1'),
                                 array('tag'=>'test2','counter'=>1,'slug'=>'test2'),
                                 array('tag'=>'test3','counter'=>1,'slug'=>'test3')), $tagCloudArr);
        
        $relatedTagList = $this->TaggableThing->findRelatedTags('test1');
        $expected = array('test2','test3');
        $this->assertEqual($expected, $relatedTagList->getTags());
        
        $tag = new Tag();
        $test1Tag = $tag->findFirstBy('name','test1');
        $this->assertEqual('test1',$test1Tag->name);
        
        $test1Tag->destroy();
        
        $relatedTagList = $this->TaggableThing->findRelatedTags('test1');
        $this->assertEqual(array(), $relatedTagList->getTags());
        
        $test1Tag = $tag->findFirstBy('name','test1');
        $this->assertEqual(false,$test1Tag);
        
        $tagCloud->reset();
        $tagCloudArr = $tagCloud->get();
        $this->assertEqual(array(array('tag'=>'test2','counter'=>1,'slug'=>'test2'),
                                 array('tag'=>'test3','counter'=>1,'slug'=>'test3')), $tagCloudArr);
    }
    function test_destroy_tagging()
    {
        $tagCloud = new TagCloud('TaggableThing');
        $this->TaggableThing->name='test2';
        $this->TaggableThing->set_tags('test3,test2,test1');
        $this->TaggableThing->save();
        
        $tagCloudArr = $tagCloud->get();
        $this->assertEqual(array(array('tag'=>'test1','counter'=>1,'slug'=>'test1'),
                                 array('tag'=>'test2','counter'=>1,'slug'=>'test2'),
                                 array('tag'=>'test3','counter'=>1,'slug'=>'test3')), $tagCloudArr);
        
        $relatedTagList = $this->TaggableThing->findRelatedTags('test1');
        $expected = array('test2','test3');
        $this->assertEqual($expected, $relatedTagList->getTags());
        
        $tag = new Tag();
        $test1Tag = $tag->findFirstBy('name','test1');
        
        $tagging = new Tagging();
        $test1Tagging = $tagging->findFirstBy('tag_id AND taggable_id',$test1Tag->id, $this->TaggableThing->id);
        $this->assertEqual($test1Tag->id,$test1Tagging->tag_id);
        
        $test1Tagging->destroy();
        
        $relatedTagList = $this->TaggableThing->findRelatedTags('test1');
        $this->assertEqual(array(), $relatedTagList->getTags());
        
        $test1Tag = $tag->findFirstBy('name','test1');
        $this->assertEqual('test1',$test1Tag->name);
        
        $tagCloud->reset();
        $tagCloudArr = $tagCloud->get();
        $this->assertEqual(array(array('tag'=>'test2','counter'=>1,'slug'=>'test2'),
                                 array('tag'=>'test3','counter'=>1,'slug'=>'test3')), $tagCloudArr);
    }
}
?>