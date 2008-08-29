<?php


class ActsAsTaggableInstaller extends AkPluginInstaller
{
    var $dependencies = array('acts_as_sluggable');

    function up_1()
    {
        $this->runMigration();
        echo "\n\nInstallation completed\n";
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
        echo "Uninstalling the acts_as_taggable plugin migration\n";
        $Installer->uninstall();
    }

}
?>