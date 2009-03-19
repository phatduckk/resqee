<?php

function p($x, $str = null)
{
    if ($str) echo "<h2>$str</h2>";
    echo '<pre>', print_r($x, true), '</pre><hr>';
}

function __autoload($className)
{
    $filePath = str_replace('_', '/', $className) . '.php';
    require_once $filePath;
}

$controller = Resqee_Controller::factory($_SERVER);
$action     = $controller->getAction();
$controller->$action();

?>