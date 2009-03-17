<?php

abstract class ReSQee_Controller
{
    public function __construct()
    {

    }

    public static function factory($uri)
    {
        $pathParts = explode('/', trim($uri, '/'));

        $controllerBaseName = (isset($pathParts[0]))
            ? ucfirst($pathParts[0])
            : 'Index';

        $controllerName = "ReSQee_Controller_{$controllerBaseName}";
    }
}
?>