<?php
require_once(AK_APP_DIR.DS.'models'.DS.'tag_cloud.php');

class TagcloudHelper extends AkActionViewHelper
{
    
    function tag_cloud($obj, $classes = null, $options = array())
    {
        $tagcount_max = 0;
        $tagcount_min = PHP_INT_MAX;
        if (is_array($obj)) {
            $tags = $obj;
            $totalCount = 0;
            foreach($tags as $idx=>$t) {
                if (!(is_array($t) && isset($t['counter']))) {
                    $tags[$idx] = array('tag'=>$t,'counter'=>1);
                }

            }
        } else if (is_object($obj) && method_exists($obj,'getTags')) {
            
            
            $tagCloud = new TagCloud($obj);
            $tags = $tagCloud->get();
            $totalCount = $tagCloud->getTotalCount();
        } else if (is_string($obj)) {
            $tagCloud = new TagCloud($obj);
            $tags = $tagCloud->get();
            $totalCount = $tagCloud->getTotalCount();
        }
        if ($classes == null) {
            $classes = array('l-popular','popular','v-popular','vv-popular','vvv-popular');
        }
        foreach ($tags as $idx=>$tag) {
                $tagcount_min = min($tag['counter'], $tagcount_min);
                $tagcount_max = max($tag['counter'], $tagcount_max);
        }
        foreach ($tags as $idx=>$tag) {
            $very_small = 0.000001;
            $index = floor (
                             ( $tag['counter'] - $tagcount_min )
                             / ( $tagcount_max - $tagcount_min + $very_small ) * count($classes)
                             );
            $tags[$idx]['class']=$classes[$index];
            if (isset($options['link'])) {
                $tags[$idx]['link'] = str_replace(':tag',$tag['slug'],$options['link']);
            }
             $tags[$idx]['tag'] = htmlentities(utf8_decode($tags[$idx]['tag']));
        }
        $default_options = array('template'=>'tag_cloud/base.tpl',
                                 'stylesheet'=>'/stylesheets/tag_cloud.css');
        $options = array_merge($default_options, $options);
        $this->_controller->tc_stylesheet = $options['stylesheet'];
        $this->_controller->tc_tags = $tags;
        return $this->_controller->renderPartial($options['template']);
        
    }
}
?>