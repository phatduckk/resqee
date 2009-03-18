<?php

class TestJob extends ReSQee_Job
{
    public function run()
    {
        return array("hello");
    }
}

?>