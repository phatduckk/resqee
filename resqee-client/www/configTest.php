<?php

require_once 'config.php';
require_once 'TestJob.php';
require_once 'Resqee/Config/Jobs.php';
require_once 'Resqee.php';

define('RESQEE_SERVER_PLUGIN_JOB_RUN_BEFORE', Resqee::SERVER_PLUGIN_JOB_RUN_BEFORE);
define('RESQEE_SERVER_PLUGIN_JOB_RUN_AFTER', Resqee::SERVER_PLUGIN_JOB_RUN_AFTER);
define('RESQEE_SERVER_PLUGIN_JOB_RUN_BOTH', Resqee::SERVER_PLUGIN_JOB_RUN_BOTH);

$conf = parse_ini_file('resqee-server.ini', 1);

foreach ($conf['plugins'] as $name => $v) {
    if ($v == Resqee::SERVER_PLUGIN_JOB_RUN_BOTH) {
        p ("$name: both");
    } else if ($v == Resqee::SERVER_PLUGIN_JOB_RUN_BEFORE) {
        p ("$name: before");        
    } else if ($v == Resqee::SERVER_PLUGIN_JOB_RUN_AFTER) {
        p ("$name: after");        
    } else {
        p ("$name: none");                
    }
}

p($conf);

/***
$config = Resqee_Config_Jobs::getInstance();

$job = new TestJob();
$availableServers = $config->getServer($job);
p($availableServers, "server that can run " . get_class($job));

p($config, "Resqee_Config_Jobs");
*****/
?>