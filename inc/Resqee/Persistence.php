<?php

require_once 'Resqee/Exception/Persistence.php';

abstract class Resqee_Persistence
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
     * Abstract method that queues a job in the persistant store
     *
     * @param string $jobId          The jobId
     * @param string $job            Serialized job class
     * @param string $args           Serizlized arguments to run()
     * @param string $jobClass       The jobs class name
     * @param string $jobParentClass The job's parent class name
     * @param int    $requestTime    Unixtime of the request
     */
    public abstract function queue(Resqee_Persistence_Item $item);

    /**
     * Complete a run of the job.
     *
     * You can examine the $response and see if any exceptions were throwin,
     * errors were raised etc
     *
     * @param string          $jobId
     * @param Resqee_Response $response
     */
    public abstract function completeJob($jobId, Resqee_Response $response);

    /**
     * Find jobs that fit the criteria specified in the $params
     *
     * @param Resqee_Persistence_SearchParams $params Search parameters
     *
     * @return array The method must return an array with 2 fields: jobs & total
     *  the 'jobs' index must return an array Resqee_Persistence_
     *  <code>
     *      array(
     *          'requests' => $foundJobs,
     *          'total'    => $numResultsWithoutOffsetAndLimit
     *      );
     *  </code>
     */
    public abstract function findJobs(Resqee_Persistence_SearchParams $params);

}

?>