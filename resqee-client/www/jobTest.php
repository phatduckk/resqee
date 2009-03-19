<?php

require_once 'config.php';
require_once 'ReSQee/Job.php';

$job = new TestJob();
p($job->fire(FALSE));
//p($job->getResult());

?>