<?php

require_once 'Resqee/Config.php';

class Resqe_Plugins
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
    const PLUGIN_EVENT_JOB_RUN_BEFORE = 2;

    /**
     * Const for server plugins that init after we run() a job
     *
     * @var int
     */
    const PLUGIN_EVENT_JOB_RUN_AFTER = 3;

    /**
     * Const for server plugins that init before and after we run() a job
     *
     * @var int
     */
    const PLUGIN_EVENT_JOB_RUN_BOTH = 1;


    /**
     * Array holding a list of the plugins we've already loaded
     *
     * @var array
     */
    private static $loaded = array();

    /**
     * Load plugins for a specific event
     *
     * @param int $event
     */
    public static function load($event)
    {
        $config = Resqee_Config::getInstance();
    }

    /**
     * Get an array of plugins
     *
     * @param string $event What event you want the plugin to be enabled for
     *
     * @return array
     */
    public static function getPlugins($event = null, $strict = false)
    {
        $config = Resqee_Config::getInstance()->getConfigSection(self::KEY_PLUGINS);

        if (empty($config)) {
            return array();
        }

        if (! $event) {
            return $config;
        }

        $rtn  = array();
        $event = (int) $event;

        foreach ($config as $k => $v) {
            $v    = (int) $v;
            $cond = (! $strict) ? ($v == $event) : false;

            if ($v == $event || $cond) {
                $rtn[$k] = $v;
            }
        }

        return $rtn;
    }
}

?>