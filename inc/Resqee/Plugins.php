<?php

require_once 'Resqee.php';
require_once 'Resqee/Config.php';

class Resqee_Plugins
{
    /**
     * Section name in resqee-server.ini for plugins
     *
     * @var string
     */
    const KEY_PLUGINS = 'plugins';

    /**
     * Array holding a list of the plugins we've already loaded
     *
     * @var array
     */
    private static $loaded = array();

    /**
     * Get an array of plugins
     *
     * If $instanciate is true then the returned array will have instanciated
     * plugin objects that are ready for use.
     *
     * @param string $event       What event you want the plugin to be enabled for
     * @param bool   $instanciate Whether or not to instanciate the plugins
     *
     * @return array
     */
    public static function getPlugins($event, $instanciate = true)
    {
        $config = Resqee_Config::getInstance()->getConfigSection(self::KEY_PLUGINS);

        if (empty($config)) {
            return array();
        }

        $rtn = array();

        foreach ($config as $pluginClass => $eventName) {
            if ($event == $eventName) {
                if ($instanciate) {
                    Resqee::loadClass($pluginClass);
                    $rtn[$pluginClass] = new $pluginClass();
                } else {
                    $rtn[] = $pluginClass;
                }
            }
        }

        return $rtn;
    }
}

?>