<?php

abstract class Resqee_Job
{
    /**
     * The job's ID
     *
     * @var string
     */
    private $jobId = null;

    /**
     * The schema, hostname & port of the server that will run this job
     *
     * @var string
     */
    private $jobServer = null;

    /**
     * Whether or not the job is fired
     *
     * @var unknown_type
     */
    private $isJobFired = false;

    /**
     * Whether of not the job will run asynchronously
     *
     * @var bool
     */
    private $isAsyc = false;

    /**
     * Resource to the socket we'll be using for connecting to the server
     * when runnning a job asynchronously
     *
     * @var resource
     */
    private $socket = null;

    /**
     * The result of the job
     *
     * @var mixed
     */
    private $result;

    /**
     * This is the method that does the actual work for your job
     *
     * @return mixed
     */
    public abstract function run();

    /**
     * Fire/Queue the job.
     *
     * This method does not actually run the job:
     * Your job gets set up and sent over to one of the available Resqee servers
     * to do the actual work.
     *
     * You can run this job synchronously or asynchronously
     *
     * @param bool $async Specify if the job should run aynchronously or not
     *
     * @return mixed If $async then the jobID is returned; else the actual result
     */
    public function fire($async = true)
    {
        $this->isAsyc  = $async;
        $this->jobId   = $this->generateJobId();
        $serializedJob = serialize($this);

        $this->execute($serializedJob);

        if ($async) {
            return $this->jobId;
        } else {
            return $this->getResult();
        }
    }

    /**
     * Get the ID of this job.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * Generate and set the jobId
     *
     * @return void
     */
    private function generateJobId()
    {
        $salt = mt_rand() . microtime(true) . print_r($_SERVER, true);

        return sha1(serialize($this) . $salt);
    }

    /**
     * Get the hostname/ip of the server that will run this job.
     *
     * If a server hasn't been picked yet we'll go ahead and pick one
     *
     * @return string
     */
    private function getJobServer()
    {
        if ($this->jobServer == null) {
            // TODO: need to read this from a config file
            $this->jobServer = array(
                'host' => 'server.resqee.local',
                'port' => 80
            );
        }

        return $this->jobServer;
    }

    /**
     * Fire off the job asynchronously
     *
     * @return string The job's ID
     */
    private final function execute($serializedJob)
    {
        $postData     = 'job=' . ($serializedJob);
        $jobServer    = $this->getJobServer();
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        $res = socket_connect(
            $this->socket,
            $jobServer['host'],
            $jobServer['port']
        );

        socket_set_block($this->socket);

        if ($res === false) {
            throw new Resqee_Exception(
                socket_strerror(),
                socket_last_error()
            );
        }

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


//        socket_write($this->socket, "\r\njob=" . urlencode($serializedJob) . "\r\n\r\n");
    }

    /**
     * Get the result of the job
     *
     * @return mixed
     */
    public function getResult()
    {
        if (! isset($this->result)) {
            $res = $b = null;

            while ($b = socket_read($this->socket, 8096)) {
                $res .= $b;
            }

            socket_close($this->socket);

            $parts = explode("\r\n\r\n", $res);
            array_shift($parts);
            $this->result = unserialize(implode("\r\n\r\n", $parts));
        }

        return $this->result;
    }
}

?>