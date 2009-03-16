<?php

abstract class ReSQee_Job
{
    /**
     * Job arguments
     *
     * @var array
     */
    private $args = array();

    /**
     * The job's ID
     *
     * @var string
     */
    private $jobId = null;

    /**
     * The hostname or IP of the server that will handle this job
     *
     * @var string
     */
    private $jobServer = null;

    /**
     * This is the method that does the actual work for your job
     *
     * @param array $args Arguments for the job
     *
     * @return mixed
     */
    public abstract function run(array $args = array());

    /**
     * Fire/Queue the job.
     *
     * This method does not actually run the job:
     * Your job gets set up and sent over to one of the available ReSQee servers
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
        $serializedJob = serialize($this);

        return ($async)
            ? $this->executeAsync($serializedJob)
            : $this->executeSync($serializedJob);
    }

    /**
     * Get the ID of this job.
     *
     * If no ID exists one will be generated
     *
     * @return string
     */
    public function getJobId()
    {
        if ($this->jobId == null) {
            $this->jobId = md5();
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
    public function getJobServer()
    {
        if ($this->jobServer == null) {

        }

        return $this->jobServer;
    }

    /**
     * Fire off the job synchronously
     *
     * @return mixed The job's result
     */
    private function executeSync($serializedJob)
    {

    }

    /**
     * Fire off the job asynchronously
     *
     * @return string The job's ID
     */
    private function executeAsync($serializedJob)
    {

    }
}

?>