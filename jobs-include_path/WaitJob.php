<?php

require_once 'Resqee/Job.php';

class WaitJob extends Resqee_Job
{
    public function run($duration = 3)
    {
        $startTime = microtime(TRUE);
        sleep($duration);
        return "Successfully slept for " . round((microtime(TRUE) - $startTime), 2)
                . " seconds.";
    }
}

?>
