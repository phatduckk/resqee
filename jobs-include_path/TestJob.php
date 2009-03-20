<?php

require_once 'Resqee/Job.php';
require_once 'TestException.php';
require_once 'User.php';

class TestJob extends Resqee_Job
{
    public $mode = null;

    public $wait = 0;

    public function run()
    {
        sleep($this->wait);

        switch ($this->mode)
        {
            case 'complex array' :
                return array(
                    'fruit' => array('apple', 'orange'),
                    'car' => array(
                        'trunk' => array('dodge ram', 'ford 150'),
                        'fancy' => array('M3', 'audi tt')
                    )
                );
            case 'stderr' :
                $foo+1;
                $boo = 7/0;
                return "check the stderr - you should see stuff there";
            case 'array' :
                return array("hello");
            case 'custom class' :
                $u = new User(12345, 'phatduckk');
                return $u;
            case 'stdClass' :
                $x = new stdClass;
                $x->foo = 'im an stdclass';
                $x->bar = 123456;
                return $x;
            case 'exception' :
                throw new Exception("this job threw an exception");
                break;
            case 'custom exception' :
                throw new TestException("custom exception class");
                break;
            case 'number';
                return 11;
            case 'resource' :
                $fp = fopen(__FILE__, 'r');
                return $fp;
            case 'output' :
                echo "this is stray output in the job";
                return "check the job's stdout in the var_dump";
                break;
            default       :
            case 'string' :
                return "resqee > resqueue";

        }
    }
}

?>