<?php

require_once 'Resqee/Plugin.php';
require_once 'Resqee/Exception/Persistence.php';
require_once 'Resqee/Persistence/Item.php';
require_once 'Resqee/Persistence/SearchParams.php';

abstract class Resqee_Persistence extends Resqee_Plugin
{
    /**
     * Queue a job in some persistent store
     *
     * This runs before the job's run() method.
     *
     * @param Resqee_Persistence_Item $item The persistence item
     *
     * @return bool
     */
    public function before(Resqee_Persistence_Item $item)
    {
        return $this->queue($item);
    }

    /**
     * Mark a job as completed in persistent store
     *
     * This will run after you job has been ran
     *
     * @param string          $jobId    The job'd id
     * @param Resqee_Response $response The response to the client
     *
     * @return voids
     */
    public function after($jobId, Resqee_Response $response)
    {
        return $this->completeJob($jobId, $response);
    }

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
     *  the 'jobs' index must contain an array Resqee_Persistence_Item objects and
     *  the 'total' field must contain the total # of jobs that matched the criteria
     *
     *  <code>
     *      array(
     *          'jobs'  => $foundJobs,
     *          'total' => $numResultsWithoutOffsetAndLimit
     *      );
     *  </code>
     */
    public abstract function findJobs(Resqee_Persistence_SearchParams $params);

    /**
     * Remove a job from the queue
     *
     * @param unknown_type $jobId
     */
    public abstract function dequeue($jobId);

}

?>