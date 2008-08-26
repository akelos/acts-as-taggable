<?php


define('AK_AAT_PLUGIN_FILES_DIR', AK_APP_PLUGINS_DIR.DS.'acts_as_taggable'.DS.'installer'.DS.'files');


class ActsAsTaggableInstaller extends AkPluginInstaller
{
   
    var $_newModelMethods = array('findRelatedTags'=>'
    function findRelatedTags($tags, $options = array()) {
        if (isset($this->taggable) && method_exists($this->taggable,"findRelatedTags")) {
            return $this->taggable->findRelatedTags($tags, $options);
        }
        return false;
    }
    ',
    '&findTaggedWith'=>'
    function &findTaggedWith($args, $options = array()) {
        $false = false;
        if (isset($this->taggable) && method_exists($this->taggable,"findTaggedWith")) {
            return $this->taggable->findTaggedWith($args, $options);
        }
        return $false;
    }
    ',
    'set_tags'=>'
    function set_tags() {
        $args = func_get_args();
        if (isset($this->taggable) && method_exists($this->taggable,"setTags")) {
            return call_user_func_array(array(&$this->taggable,"setTags"),$args);
        }
        return false;
    }
    ',
    'get_tags'=>'
    function get_tags() {
        if (isset($this->taggable) && method_exists($this->taggable,"getTags")) {
            return $this->taggable->getTags();
        }
        return false;
    }
    ',
    'get_safe_tags'=>'
    function get_safe_tags() {
        if (isset($this->taggable) && method_exists($this->taggable,"getSafeTags")) {
            return $this->taggable->getSafeTags();
        }
        return false;
    }
    ',
    'addTag'=>'
    function addTag($tag) {
        if (isset($this->taggable) && method_exists($this->taggable,"addTag")) {
            return $this->taggable->addTag($tag);
        }
        return false;
    }
    ',
    'addTags'=>'
    function addTags() {
        $args = func_get_args();
        if (isset($this->taggable) && method_exists($this->taggable,"addTags")) {
            return call_user_func_array(array(&$this->taggable,"addTags"),$args);
        }
        return false;
    }
    ',
    'removeTag'=>'
    function removeTag($tag) {
        if (isset($this->taggable) && method_exists($this->taggable,"removeTag")) {
            return $this->taggable->removeTag($tag);
        }
        return false;
    }
    ',
    'removeTags'=>'
    function removeTags($tag) {
        $args = func_get_args();
        if (isset($this->taggable) && method_exists($this->taggable,"removeTags")) {
            return call_user_func_array(array(&$this->taggable,"removeTags"),$args);
        }
        return false;
    }
    ',
    '&get_tag_list'=>'
    function &get_tag_list() {
        $false = false;
        if (isset($this->taggable) && method_exists($this->taggable,"getTagList")) {
            $tagList = &$this->taggable->getTagList();
            return $tagList;
        }
        return $false;
    }
    ',
    '&xinstantiate'=>'
    function &xinstantiate($record, $set_as_new = true) {
        $object = &parent::instantiate($record, $set_as_new);
        if (isset($object->taggable) && method_exists($object->taggable,"load")) {
            $object->taggable->load();
        }
        return $object;
    }
    ',
    'get_tag_type'=>'
    function get_tag_type() {
        if (!isset($this->__taggable_type)) {
            $this->__taggable_type = strtolower($this->getTableName());
        }
        
        return $this->__taggable_type;
    }
    ');
    function _checkSluggablePlugin()
    {
        $frameworkPluginExists = file_exists(AK_BASE_DIR.DS.'vendor'.DS.'plugins'.DS.'acts_as_sluggable'.DS.'lib'.DS.'ActsAsSluggable.php');
        $appPluginExists = file_exists(AK_APP_DIR.DS.'vendor'.DS.'plugins'.DS.'acts_as_sluggable'.DS.'lib'.DS.'ActsAsSluggable.php');
        if (!$frameworkPluginExists && !$appPluginExists) {
            die("\nActsAsTaggable depends on the plugin ActsAsSluggable. Please install first.\n\n");
        }
    }
    
    function up_1()
    {
        $this->_checkSluggablePlugin();
        $this->files = Ak::dir(AK_AAT_PLUGIN_FILES_DIR, array('recurse'=> true));
        empty($this->options['force']) ? $this->checkForCollisions($this->files) : null;
        $this->copyFiles();

        echo "\nAdding methods to shared_model.php.\n\n ";
        $this->addNewMethodsToSharedModel();
        $this->runMigration();
        echo "\n\nInstallation completed\n";
    }
    function addNewMethodsToSharedModel()
    {
        foreach ($this->_newModelMethods as $name=>$method) {
            echo "Adding method ActiveRecord::$name method: ";
            $res = $this->addMethodToBaseAR($name,$method);
            echo $res===true?'[OK]':'[FAIL]:'."\n-- ".$res;
            echo "\n";
        }
    }
    function removeNewMethodsFromSharedModel()
    {
        foreach ($this->_newModelMethods as $name=>$method) {
            $this->removeMethodFromBaseAR($name);
        }
    }
    
    
    
    function copyFiles()
    {
        $this->_copyFiles($this->files);
    }
    function _copyFiles($directory_structure, $base_path = AK_AAT_PLUGIN_FILES_DIR)
    {
        foreach ($directory_structure as $k=>$node){
            $path = $base_path.DS.$node;
            if(is_dir($path)){
                echo 'Creating dir '.$path."\n";
                $this->_makeDir($path);
            }elseif(is_file($path)){
                echo 'Creating file '.$path."\n";
                $this->_copyFile($path);
            }elseif(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        echo 'Creating dir '.$path."\n";
                        $this->_makeDir($path);
                        $this->_copyFiles($items, $path);
                    }
                }
            }
        }
    }

    function _makeDir($path)
    {
        $dir = str_replace(AK_AAT_PLUGIN_FILES_DIR, AK_BASE_DIR,$path);
        if(!is_dir($dir)){
            mkdir($dir);
        }
    }

    function _copyFile($path)
    {
        $destination_file = str_replace(AK_AAT_PLUGIN_FILES_DIR, AK_BASE_DIR,$path);
        copy($path, $destination_file);
        $source_file_mode =  fileperms($path);
        $target_file_mode =  fileperms($destination_file);
        if($source_file_mode != $target_file_mode){
            chmod($destination_file,$source_file_mode);
        }
    }
    function checkForCollisions(&$directory_structure, $base_path = AK_AAT_PLUGIN_FILES_DIR)
    {
        foreach ($directory_structure as $k=>$node){
            if(!empty($this->skip_all)){
                return ;
            }
            $path = str_replace(AK_AAT_PLUGIN_FILES_DIR, AK_BASE_DIR, $base_path.DS.$node);
            if(is_file($path)){
                $message = Ak::t('File %file exists.', array('%file'=>$path));
                $user_response = AkInstaller::promptUserVar($message."\n d (overwrite mine), i (keep mine), a (abort), O (overwrite all), K (keep all)", 'i');
                if($user_response == 'i'){
                    unset($directory_structure[$k]);
                }    elseif($user_response == 'O'){
                    return false;
                }    elseif($user_response == 'K'){
                    $directory_structure = array();
                    return false;
                }elseif($user_response != 'd'){
                    echo "\nAborting\n";
                    exit;
                }
            }elseif(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        if($this->checkForCollisions($directory_structure[$k][$dir], $path) === false){
                            $this->skip_all = true;
                            return;
                        }
                    }
                }
            }
        }
    }
    function runMigration()
    {
        include_once(AK_APP_INSTALLERS_DIR.DS.'acts_as_taggable_plugin_installer.php');
        $Installer =& new ActsAsTaggablePluginInstaller();

        echo "Running the acts_as_taggable plugin migration\n";
        $Installer->install();
    }

    function down_1()
    {
        include_once(AK_APP_INSTALLERS_DIR.DS.'acts_as_taggable_plugin_installer.php');
        $Installer =& new ActsAsTaggablePluginInstaller();
        $this->removeNewMethodsFromSharedModel();
        echo "Uninstalling the acts_as_taggable plugin migration\n";
        $Installer->uninstall();
    }

}
?>