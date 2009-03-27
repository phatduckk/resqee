<?php

class Resqee_Config
{
    /**
     * The config data
     *
     * @var array
     */
    protected $config = array();

    /**
     * And instance of this class
     *
     * @var Resqee_Config
     */
    protected static $instance = null;

    /**
     * const used to fetch/add the config array in APC
     *
     * @var string
     */
    const APC_KEY_CONFIG = 'RESQEE_CONFIG_';

    /**
     * Base key for use in APC for a disabled server flag
     *
     * @var string
     */
    const APC_KEY_SERVER_DISABLED = 'APC_KEY_SERVER_DISABLED.';

    /**
     * const to determine how long we store the config in apc
     *
     * TODO: maybe make this configurable
     *
     * @var string
     */
    const APC_TTL_CONFIG = 300;

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
        return 'resqee.ini';
    }

    /**
     * Get an instance of
     *
     * @return Resqee_Config
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Resqee_Config();
        }

        return self::$instance;
    }

    /**
     * Protected constructor
     *
     * @return void
     */
    protected function __construct()
    {
        if (! defined('RESQEE_CONSTANTS')) {
            define('RESQEE_PLUGIN_JOB_RUN_BEFORE', Resqe_Plugins::PLUGIN_EVENT_JOB_RUN_BEFORE);
            define('RESQEE_PLUGIN_JOB_RUN_AFTER' , Resqe_Plugins::PLUGIN_EVENT_JOB_RUN_AFTER);
            define('RESQEE_PLUGIN_JOB_RUN_BOTH'  , Resqe_Plugins::PLUGIN_EVENT_JOB_RUN_BOTH);
            define('RESQEE_CONSTANTS', true);
        }

        $isAPCEnabled = Resqee::isAPCEnabled();

        if ($isAPCEnabled) {
            $config = apc_fetch(self::APC_KEY_CONFIG . $this->getConfigFile());

            if ($config !== false) {
                $this->config = $config;
            }
        }

        if (empty($this->config)) {
            $this->config = $this->parseConfig($this->getConfigFile());

            if ($isAPCEnabled) {
                apc_add(
                    self::APC_KEY_CONFIG . $this->getConfigFile(),
                    $this->config,
                    self::APC_TTL_CONFIG
                );
            }
        }
    }

    /**
     * Get the value of key from the config (ini file)
     *
     * @param string $key     The key
     * @param string $section The section
     *
     * @return string
     */
    public function get($key, $section = null)
    {
        if ($section == null) {
            return (isset($this->config[$key]))
                ? $this->config[$key]
                : null;
        }

        return (isset($this->config[$section], $this->config[$section][$key]))
            ? $this->config[$section][$key]
            : null;
    }

    /**
     * Get all the ini keys & values for a section
     *
     * @return array
     */
    public function getConfigSection($section)
    {
        return ($this->config[$section])
            ? $this->config[$section]
            : array();
    }

    /**
     * Method to parse the config file
     *
     * @param string $file The name of the ini file
     */
    protected function parseConfig($file)
    {
        return parse_ini_file($file, true);
    }
}

?>