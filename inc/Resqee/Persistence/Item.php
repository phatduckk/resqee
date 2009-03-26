<?php

class Resqee_Persistence_Item
{
    /**
     * const for a completed job w/o exception
     *
     * This is used when a job is completed. It has no bearing on
     * whether there were any PHP errors or not. However all these jobs should
     * NOT have thrown an exception
     *
     * @var int
     */
    const STATUS_COMPLETE_OK = '1';

    /**
     * const for failed jobs
     *
     * The job has completed but threw an exception
     *
     * @var int
     */
    const STATUS_COMPLETE_FAILED = '2';

    /**
     * const for queued job
     *
     * @var int
     */
    const STATUS_QUEUED = '4';

    /**
     * The jobId to persist
     *
     * @var string
     */
    public $jobId;

    /**
     * The serialized job to persist
     *
     * @var string
     */
    public $job;

    /**
     * The serialized arguments to persist
     *
     * @var srtring
     */
    public $args;

    /**
     * The job's class name
     *
     * @var string
     */
    public $jobClass;

    /**
     * The job's parent's class name
     *
     * @var string
     */
    public $jobParentClass;

    /**
     * The unixtime for when the job was first requested/queued
     *
     * @var int
     */
    public $requestTime;

    /**
     * The unixtime for when a response was sent
     *
     * @var int
     */
    public $responseTime;

    /**
     * boolean denoting if the job had PHP erros
     *
     * @var bool
     */
    public $hasErrors;

    /**
     * boolean denoting if the job wrote to stdout
     *
     * @var bool
     */
    public $hasStdout;

    /**
     * The serialized response
     *
     * @var string
     */
    public $response;

    /**
     * The job's class name
     *
     * @var string
     */
    public $class;

    /**
     * The job's parent's class name
     *
     * @var string
     */
    public $parentClass;

    /**
     * The unserialized arguments to the job
     *
     * @var array
     */
    private $_args = null;

    /**
     * The unserialized job
     *
     * @var Resqee_Job
     */
    private $_job = null;

    /**
     * Get the unserialized arguments to the job
     *
     * @return array
     */
    public function getArgs()
    {
        if (!isset($this->_args)) {
            $this->_args = unserialize($this->args);
        }

        return $this->_args;
    }

    /**
     * Get the unserialized job
     *
     * @return Resqee_job
     */
    public function getJob()
    {
        if (!isset($this->_job)) {
            $this->_job = unserialize($this->job);
        }

        return $this->_job;
    }

}

?>