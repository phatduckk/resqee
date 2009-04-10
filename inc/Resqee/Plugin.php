<?php

abstract class Resqee_Plugin
{
    /**
     * Method to run before an event
     *
     * You should override this method. Feel free to leave it blank. AKA have
     * it do nothing
     *
     * @return void
     */
    public function before(){}

    /**
     * Method to run after an event
     *
     * You should override this method. Feel free to leave it blank. AKA have
     * it do nothing
     *
     * @return mixed
     */
    public function after(){}

    /**
     * Get the configuration for this plugin
     *
     * The plugin's configuration info should be in a section in resqee.ini
     * example: if your plugin's class name is MyPlugin then you should have
     * something like the following in resqee.ini
     *
     * [MyPlugin]
     * myPlugin.variable1 = true
     * myPlugin.variable2 = 12345
     * myPlugin.db.host   = localhost
     * myPlugin.db.port   = 3306
     * myPlugin.db.user   = resqeeUser
     * myPlugin.db.passwd = resqeePassword
     *
     * Also NOTE: Your plugin must be enabled in the [plugins] section of resqee.ini
     * To enable a plugin do one of the following:
     *
     * [plugins]
     * Plugin1ClassName = <eventID>         ; run the plugin's before() and after() methods for <eventID>
     * Plugin2ClassName = <eventID>"before" ; only run the plugin's before() method for <eventID>
     * Plugin3ClassName = <eventID>"after"  ; only run the plugin's after() method for <eventID>
     * Plugin4ClassName = 1after            ; only run the plugin's after() method for event w/ id of 1
     *
     * You can use the keys in Resqee_Plugin_Events::$events for eventIDs or their
     * values. Either will work b/c Resqee_Config pulls those keys out and turns
     * them into constants before parsing the ini file. Also, I know the " syntax sucks
     * but that's due to how parse_ini_file() works.
     *
     * @return array
     */
    public function getConfig()
    {
        return Resqee_Config::getInstance()->getConfigSection(get_class($this));
    }
}

?>