<?php

function p($x) {
    echo '<pre>', print_r($x, true), '</pre><hr>';
}

p(substr(PHP_OS, 0, 3));

// global error handler
// global exception handler
// __autoload for RiSQ_Job classes

p($_SERVER);
?>