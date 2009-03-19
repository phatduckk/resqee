<?php

require_once 'config.php';
require_once 'Resqee/Job.php';

$job = new WaitJob();
p($job->fire());
p($job->getResult());

?>
