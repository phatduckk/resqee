<?php

require_once 'Resqee/Controller.php';

function p($x, $str = null)
{
    if ($str) echo "<h2>$str</h2>";
    echo '<pre>', print_r($x, true), '</pre><hr>';
}

$controller = Resqee_Controller::factory($_SERVER);
$action     = $controller->getAction();

try {
    $controller->$action();
} catch (Exception $e) {
    print_r($e);
}

?>