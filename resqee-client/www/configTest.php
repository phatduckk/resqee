<?php

require_once 'config.php';
require_once 'TestJob.php';
require_once 'Resqee/Config/Jobs.php';

$config = Resqee_Config_Jobs::getInstance();

$job = new TestJob();
$availableServers = $config->getServer($job);
p($availableServers, "server that can run " . get_class($job));

p($config, "Resqee_Config_Jobs");
?>