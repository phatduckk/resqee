<?php

require_once 'config.php';
require_once 'Resqee/Job.php';

$job = new TestJob();
p($job->fire(FALSE));
//p($job->getResult());

?>