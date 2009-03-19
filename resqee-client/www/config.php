<?php
/**
 * constant for the server's host.
 * http:// is appended
 */
define('RESQEE_HOST', "http://" . getenv('RESQEE_SERVER'));

function __autoload($className)
{
    $filePath = str_replace('_', '/', $className) . '.php';
    require_once $filePath;
}

function p($x, $str = null)
{
    if ($str) echo "<h2>$str</h2>";
    echo '<pre>';
    var_dump($x);
    echo '</pre>';
    echo '<hr />';
}
?>