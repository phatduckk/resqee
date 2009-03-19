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
    private abstract function getConfigFile();

    /**
     * Protected constructor
     *
     * @return void
     */
    protected function __construct()
    {

    }

    /**
     *
     * @return Resqee_Config
     */
    public static function getInstance()
    {

    }
}

?>