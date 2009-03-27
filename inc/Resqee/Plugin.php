<?php

abstract class Resqee_Plugin
{
    /**
     * Method to run for an event
     *
     * You should override this method
     *
     * @return void
     */
    public function before()
    {

    }

    /**
     * Method to run after an event
     *
     * You should override this method too
     *
     * @return mixed
     */
    public function after()
    {

    }

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
     * Feel free to override this method and do your own thing
     *
     * @return array
     */
    public function getConfig()
    {
        return Resqee_Config::getInstance()->getConfigSection(get_class($this));
    }
}

?>