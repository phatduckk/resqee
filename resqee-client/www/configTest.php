<?php

require_once 'config.php';
require_once 'TestJob.php';
require_once 'Resqee/Config.php';
require_once 'Resqee/Plugins.php';
require_once 'Resqee.php';

p(Resqe_Plugins::getPlugins(), "all");
p(Resqe_Plugins::getPlugins(2), "2");
p(Resqe_Plugins::getPlugins(2, true), "2 strict");
p(Resqe_Plugins::getPlugins(1), 1);
p(Resqe_Plugins::getPlugins(3), 3);


?>