<?php
/**
 * constant for the server's host.
 * http:// is appended
 */
define('RESQEE_HOST', "http://" . getenv('RESQEE_SERVER'));

function p($x, $str = null)
{
    if ($str) echo "<h2>$str</h2>";
    echo '<pre>';
    print_r($x);
    echo '</pre>';
    echo '<hr />';
}
?>