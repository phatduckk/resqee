<?php

require_once 'Resqee/Job.php';

class TestJob extends Resqee_Job
{
    public function run()
    {
        return array("hello");
    }
}

?>