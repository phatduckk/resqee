<?php

require_once 'TestJob.php';
require_once 'Resqee.php';
require_once 'config.php';
require_once 'Resqee/Job.php';

$job = new TestJob();
$ids = array();

p('jobIds below', "calling fire twice");

$id = $job->fire('fire 1');
p("fire 1 jobId: $id");
$ids[] = $id;

$id = $job->fire('fire 2');
p("fire 2 jobId: $id");
$ids[] = $id;

p($job->block('im a blocking job'), "result of blocking job");
p($job->block(), "result of blocking job with NO ARGUMENTS");

foreach ($ids as $jobId) {
    p ($job->getResult($jobId), "result for $jobId");
}

p("", 'gonna queue 2 jobs and fire them');
$ids = array();
$ids[] = $job->queue("queued job 1");
$ids[] = $job->queue("queued job 2");

foreach ($ids as $jobId) {
    p ($job->getResult($jobId), "result for queued job $jobId");
}

p($job, "the whole job");
p($job->getResponses(), "all responses");

?>