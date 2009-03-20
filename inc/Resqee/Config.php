<?php

abstract class Resqee_Config
{
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
     * Method to parse the config file
     *
     * @param string $file The name of the ini file
     */
    protected abstract function parseConfig($file);

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
     * Protected constructor
     *
     * @return void
     */
    protected function __construct()
    {
        $this->parseConfig($this->getConfigFile());
    }

    /**
     * Get an instance
     *
     * @return Resqee_Config
     */
    public static abstract function getInstance();
}

?>