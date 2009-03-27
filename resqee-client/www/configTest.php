<?php

require_once 'config.php';
require_once 'TestJob.php';
require_once 'Resqee/Config.php';
require_once 'Resqee.php';

$c = Resqee_Config::getInstance();

p($c->getPlugins(), "all");
p($c->getPlugins(2), "2");
p($c->getPlugins(2, true), "2 strict");
p($c->getPlugins(1), 1);
p($c->getPlugins(3), 3);

p($c);

?>