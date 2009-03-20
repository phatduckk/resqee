<?php

require_once 'Resqee/Response.php';
require_once 'Resqee/Config/Jobs.php';
require_once 'Resqee/Exception/Socket.php';

abstract class Resqee_Job
{
    /**
     * The job's ID
     *
     * @var string
     */
    private $jobId = null;

    /**
     * The job config
     *
     * @var Resqee_Config_Jobs
     */
    private $jobConfig = null;

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
    private $isAsyc = true;

    /**
     * The number of times we've tried hitting the server
     *
     * @var int
     */
    private $numTries = 0;

    /**
     * Resource to the socket we'll be using for connecting to the server
     * when runnning a job asynchronously
     *
     * @var resource
     */
    private $socket = null;

    /**
     * Serialized version of this job
     *
     * @var string
     */
    private $serializedJob = null;

    /**
     * The response from the server
     *
     * @var Resqee_Response
     */
    private $response;

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
        $this->isJobFired = true;
        $this->isAsyc     = $async;
        $jobServer        = $this->getJobServer();

        $this->generateJobId();

        try {
            $this->execute($jobServer);
        } catch (Resqee_Exception_Socket $e) {
            // couldn't make a connection. Let's retry
            $this->fire($async);
        }

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
        if ($this->jobId == null) {
            $salt = mt_rand() . microtime(true) . print_r($_SERVER, true);
            $this->jobId = sha1(serialize($this) . $salt);
        }

        return $this->jobId;
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
        if ($this->numTries > 0) {
            $server = Resqee_Config_Jobs::getServer($this);

            if (! $server) {
                throw new Resqee_Exception(
                    'There are no servers configured to run this job. ' .
                    ' Add an entry for this job in <include_path>'
                );
            } else {
                $this->jobServer = $server;
            }
        }

        return $this->jobServer;
    }

    /**
     * Retry
     *
     * This method will get called if hitting a server has failed or timedout
     */
    private function retry()
    {

    }

    /**
     * Fire off the job asynchronously
     *
     * @param array $jobServer An array with host and port of server to run the
     *  job on
     *
     * @return string The job's ID
     */
    private final function execute($jobServer)
    {
        $this->numTries++;
        $this->serializedJob = serialize($this);

        $postData = Resqee::KEY_POST_JOB_PARAM . '=' . ($this->serializedJob) . '&' .
                    Resqee::KEY_POST_JOB_CLASS_PARAM . '=' . get_class($this) . '&' .
                    Resqee::KEY_POST_JOB_NUM_TRIES . '=' . $this->numTries;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        // failed creating a socket. disable the server
        if ($this->socket === false) {
            Resqee_Config_Jobs::disableServer($jobServer);

            throw new Resqee_Exception_Socket(
                socket_strerror(socket_last_error()),
                socket_last_error()
            );
        }

        $res = @socket_connect(
            $this->socket,
            $jobServer['host'],
            $jobServer['port']
        );

        // failed connecting to the socket
        if ($res === false) {
            Resqee_Config_Jobs::disableServer($jobServer);

            throw new Resqee_Exception_Socket(
                socket_strerror(socket_last_error()),
                socket_last_error()
            );
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
    }

    /**
     * Get the result of the job
     *
     * @return mixed
     */
    public function getResult()
    {
        if (! $this->isJobFired) {
            $this->fire(false);
        }

        if (! isset($this->response)) {
            $res = $b = null;

            while ($b = socket_read($this->socket, 8096)) {
                $res .= $b;
            }

            socket_close($this->socket);

            $parts = explode("\r\n\r\n", $res);
            array_shift($parts);

            $this->response = unserialize(implode("\r\n\r\n", $parts));

            if ($this->response->getException() !== null) {
                throw $this->response->getException();
            } else {
                $this->result = $this->response->getResult();
            }
        }

        return $this->result;
    }

    /**
     * Get the response that the server returned
     *
     * @return Resqee_Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}

?>