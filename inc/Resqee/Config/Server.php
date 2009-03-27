<?php

require_once 'Resqee/Config.php';

class Resqee_Config_Server extends Resqee_Config
{
    /**
     * Section name in resqee-server.ini for plugins
     *
     * @var string
     */
    const KEY_PLUGINS = 'plugins';

    /**
     * Const for server plugins that init before we run() a job
     *
     * @var int
     */
    const PLUGIN_EVENT_RUN_BEFORE = 2;

    /**
     * Const for server plugins that init after we run() a job
     *
     * @var int
     */
    const PLUGIN_EVENT_RUN_AFTER = 3;

    /**
     * Const for server plugins that init before and after we run() a job
     *
     * @var int
     */
    const PLUGIN_EVENT_RUN_BOTH = 1;

    /**
     * Get an instance of Resqee_Config_Server
     *
     * @return Resqee_Config_Server
     */
    public static function getInstance()
    {
        if (self::$instance == null) {

            define('RESQEE_PLUGIN_RUN_BEFORE', self::PLUGIN_EVENT_RUN_BEFORE);
            define('RESQEE_PLUGIN_RUN_AFTER' , self::PLUGIN_EVENT_RUN_AFTER);
            define('RESQEE_PLUGIN_RUN_BOTH'  , self::PLUGIN_EVENT_RUN_BOTH);
            self::$instance = new Resqee_Config_Server();
        }

        return self::$instance;
    }

    /**
     * Get the path to the config file
     *
     * The method must return an absolute path or a path which is
     * accessible via
     * the include_path
     *
     * @return string
     */
    public function getConfigFile()
    {
        return 'resqee-server.ini';
    }

    /**
     * Get an array of plugins
     *
     * The events to use for $event are:
     *  self::PLUGIN_EVENT_RUN_BEFORE => plugins to init before run() is called on a job
     *  self::PLUGIN_EVENT_RUN_AFTER  => plugins to init after run() is called on a job
     *  self::PLUGIN_EVENT_RUN_BOTH   => plugins to init before or after run() is called on a job
     *
     * If null is
     *
     * @param string $event What event you want the plugin to be enabled for
     *
     * @return array
     */
    public function getPlugins($event = null, $strict = false)
    {
        if (! isset($this->config[self::KEY_PLUGINS])) {
            return array();
        }

        if (! $event) {
            return $this->config[self::KEY_PLUGINS];
        }

        $rtn  = array();
        $event = (int) $event;

        foreach ($this->config[self::KEY_PLUGINS] as $k => $v) {
            $v = (int) $v;
            if (! $strict) {
                if ($v == $event || $v == 1) {
                    $rtn[$k] = $v;
                }
            } else {
                if ($v == $event) {
                    $rtn[$k] = $v;
                }
            }
        }

        return $rtn;
    }
}

?>