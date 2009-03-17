<?php

function p($x, $str = null)
{
    if ($str) echo "<h2>$str</h2>";
    echo '<pre>', print_r($x, true), '</pre><hr>';
}

function __autoload($className)
{
    require_once str_replace('_', '/', $className);
}

$controller = ReSQee_Controller::factory($_SERVER['REQUEST_URI']);
$action     = $controller->getAction();
$controller->$action();

?>