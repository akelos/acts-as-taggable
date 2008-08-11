<?php

class ActsAsTaggablePlugin extends AkPlugin
{
    function load()
    {
        require_once($this->getPath().DS.'lib'.DS.'ActsAsTaggable.php');
        $this->addHelper('tagcloud_helper');
    }
}

?>