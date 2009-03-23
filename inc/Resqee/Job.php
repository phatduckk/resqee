<?php

require_once 'Resqee/Response.php';
require_once 'Resqee/Config/Jobs.php';
require_once 'Resqee/Exception/Socket.php';

abstract class Resqee_Job
{
    /**
     * The queue of jobs
     *
     * @var array
     */
    private $queue = array();

    /**
     * A stack of exec's that have been/are-being fired
     *
     * @var array
     */
    private $execStack = array();

    /**
     * The last jobId we queued/fired
     *
     * @var string
     */
    private $lastJobId = null;

    /**
     * Resource to the socket we'll be using for connecting to the server
     * when runnning a job asynchronously
     *
     * @var resource
     */
    private $socket = null;

    /**
     * The responses from the server
     *
     * @var array
     */
    private $responses = array();

    /**
     * This is the method that does the actual work for your job
     *
     * NOTE: you can make your class' run() method take as many parameters as you
     * want. So you implementation can look something like:
     *
     * <code>
     * public function run($a, $b)
     * {
     *     return $a + $b;
     * }
     * </code>
     *
     * Any arguments you pass to fire() or getResult() will be proxied over to
     * your method
     *
     * @return mixed
     */
    public abstract function run();

    /**
     * Asynchronously fire off a new job if the queue is empty. Otherwise fire
     * every job in the queue asynchronously in a single HTTP request.
     *
     * This job performs one of 2 actions:
     *  1) If any jobs have been queued it will fire off all the queued jobs.
     *  2) If no jobs have been queued, or if the queue if empty it will queue
     *     up a new job with no arguments are fire it.
     *
     * This function can optionally take an infinite # of arguments. Any arguemnt
     * you pass in will be used when calling you class' run() method on the server.
     *
     * @args mixed [mixed $... ] Any number of variables of any type. Any arguemnt
     *  you pass in will be used when calling you class' run() method on the server.
     *
     * @return array|string If the queue if not empty you'll be returned the ID
     *  of every job we fire (array). If the queue is empty then you'll be
     *  returned the new job's ID (string).
     */
    public function fire()
    {
        $rtn = null;

        if (empty($this->queue)) {
            if (func_num_args() !== 0) {
                $args = func_get_args();
                $rtn  = call_user_func_array(array($this, 'queue'), $args);
            } else {
                $rtn = $this->queue();
            }
        } else {
            $rtn = array_keys($this->queue);
        }

        $this->execQueuedJobs();
        return $rtn;
    }

    /**
     * Take all queued jobs and execute them
     *
     * @return array The jobId's that we're executing
     */
    private function execQueuedJobs()
    {
        $jobs            = $this->queue;
        $this->queue     = array();
        $this->execStack = array_merge($this->execStack, $jobs);

        return $this->execute($jobs);
    }

    /**
     * Queue a job which we'll be running later.
     *
     * This method is useful if you want to perform a bunch of jobs on the server
     * at the same time.
     *
     * @args mixed [mixed $... ] Any number of variables of any type. Any arguemnt
     *  you pass in will be used when calling you class' run() method on the server.
     *
     * @return string The ID of the job
     */
    public function queue()
    {
        $args  = (func_num_args()) ? func_get_args() : null;
        $ser   = serialize($this);
        $args  = serialize($args);
        $salt  = mt_rand() . microtime(true) . print_r($_SERVER, true);
        $jobId = sha1($ser . $salt . $args);

        $this->queue[$jobId] = array(
            Resqee::KEY_POST_JOB_PARAM       => $ser,
            Resqee::KEY_POST_JOB_CLASS_PARAM => get_class($this),
            Resqee::KEY_POST_JOB_ARGS_PARAM  => $args,
            Resqee::KEY_POST_JOB_ID_PARAM    => $jobId
        );

        $this->lastJobId = $jobId;
        return $jobId;
    }

    /**
     * Get the hostname/ip of the server that will run this job.
     *
     * If a server hasn't been picked yet we'll go ahead and pick one
     *
     * @return array An array with an available server's hostname and post
     *  array('host' => example.com, 'port' => 80)
     */
    private function getJobServer()
    {
        $server = Resqee_Config_Jobs::getServer($this);

        if (! $server) {
            throw new Resqee_Exception(
                'There are no servers available to run this job. ' .
                ' Add an entry for this job in <include_path>'
            );
        } else {
            return $server;
        }
    }

    /**
     * Begin executing one or more job
     *
     * @param Resqee_Job|array $jobs A Resqee_Job or an array of Resqee_Job objects
     *
     * @return array The IDs of the jobs we exectued
     */
    private function execute($jobs)
    {
        if (! is_array($jobs)) {
            $jobs = array($jobs);
        }

        $postData = Resqee::KEY_POST_JOB_CLASS_PARAM . '=' . get_class($this) .
                    '&' . Resqee::KEY_POST_JOB_PARAM . '=' . serialize($jobs);

        $jobServer = null;

        while ($jobServer == null) {
            try {
                $jobServer = $this->getJobServer();
                $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            } catch (Resqee_Exception $e) {
                throw $e;
            }

            // failed creating a socket. disable the server
            if ($this->socket === false) {
                Resqee_Config_Jobs::disableServer($jobServer);
                socket_close($this->socket);

                $this->socket = $jobServer = null;
                continue;
            }

            $res = @socket_connect(
                $this->socket,
                $jobServer['host'],
                $jobServer['port']
            );

            // failed connecting to the socket
            if ($res === false) {
                Resqee_Config_Jobs::disableServer($jobServer);
                socket_close($this->socket);

                $this->socket = $jobServer = null;
                continue;
            }
        }

        socket_set_block($this->socket);

        $headers = array(
            "POST /job HTTP/1.1",
            "Host: {$jobServer['host']}",
            'User-Agent: Resqee Client',
            'Connection: Close',
            'Content-Length: ' . strlen($postData),
            "Content-Type: application/x-www-form-urlencoded",
            "Connection: close",
        );

        socket_write($this->socket, implode("\r\n", $headers));
        socket_write($this->socket, "\r\n\r\n");
        socket_write($this->socket, $postData);

        return array_keys($jobs);
    }

    /**
     * Get the result of a fired job
     *
     * @param string $jobId The ID of a job you want the results for. If you do
     *  not pass in a $jobId we'll return the result of the last job that was
     *  fired
     *
     * @return mixed The result of your job's run() method
     */
    public function getResult($jobId = null)
    {
        $jobId = (! $jobId) ? $this->lastJobId : $jobId;

        if ($jobId == null) {
            throw new Resqee_Exception("Invalid jobId: {$jobId}");
        } else if (isset($this->responses[$jobId])) {
            return $this->responses[$jobId]->getResult();
        }

        if (isset($this->queue[$jobId])) {
            $this->execQueuedJobs();
        }

        if (! $this->socket) {
            throw new Resqee_Exception('
                Could not establish a connection to any servers.
            ');
        }

        $res = $b = null;
        while ($b = socket_read($this->socket, 8096)) {
            $res .= $b;
        }

        $parts = explode("\r\n\r\n", $res);

        socket_close($this->socket);
        array_shift($parts);

        $responses       = unserialize(implode("\r\n\r\n", $parts));
        $this->responses = array_merge($this->responses, $responses);

        if (! isset($this->responses[$jobId])) {
            throw new Resqee_Exception("Invalid jobId: {$jobId}");
        } else if ($this->responses[$jobId]->getException() !== null) {
            throw $this->responses[$jobId]->getException();
        } else {
            return $this->responses[$jobId]->getResult();
        }
    }

    /**
     * Get all responses
     *
     * @array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * The the response for a job by its jobId
     *
     * If you don't pass in a jobId we'll try to get the response of the job
     *  that ran last.
     *
     * @param string $jobId The ID of the job you want the response for. If you
     *  don't pass in a $jobId we'll return the response for the last job
     *
     * @return Resqee_Response The response
     */
    public function getResponse($jobId = null)
    {
        $jobId = ($jobId) ? $jobId : $this->lastJobId;

        if (! isset($this->responses[$jobId])) {
            $this->getResult($jobId);
        }

        return $this->responses[$jobId];
    }

    /**
     * Run a job and block untill the result if available
     *
     * @args mixed [mixed $... ] Any number of variables of any type. Any arguemnt
     *  you pass in will be used when calling you class' run() method on the server.
     *
     * @return mixed The result of your job's run() method
     */
    public function block()
    {
        $args  = (func_num_args()) ? func_get_args() : null;
        $jobId = ($args)
            ? call_user_func_array(array($this, 'queue'), $args)
            : $this->queue();

        $result = $this->getResult($jobId);

        return $result;
    }
}

?>