<?php

require_once 'TestJob.php';
require_once 'Resqee.php';
require_once 'config.php';
require_once 'Resqee/Job.php';

$job = new TestJob();
$ids = array();

$id = $job->fire('something');
p("something: $id");
$ids[] = $id;

$id = $job->fire('nothing');
p("nothing: $id");
$ids[] = $id;

foreach ($ids as $jobId) {
//    p($job->getResult($jobId), "result for $jobId");
    p ($job->getResult($jobId), "result for $jobId");
}

p($job->getResponses());

?>