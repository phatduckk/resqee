<?php

abstract class Resqee_Config
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
    public abstract function getConfigFile();

    /**
     * Get an instance
     *
     * @return Resqee_Config
     */
    public static abstract function getInstance();

    /**
     * Protected constructor
     *
     * @return void
     */
    protected function __construct()
    {
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