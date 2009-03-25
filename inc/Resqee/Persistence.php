<?php

require_once 'Resqee/Exception/Persistence.php';

abstract class Resqee_Persistence
{
    /**
     * const for successfully completed job
     *
     * This is used when no exceptions are thrown
     *
     * @var int
     */
    const STATUS_SUCCESS = '1';

    /**
     * const for failed jobs
     *
     * This is used when exceptions are thrown
     *
     * @var int
     */
    const STATUS_FAILED = '2';

    /**
     * const for queued job
     *
     * @var int
     */
    const STATUS_QUEUED = '4';

    public abstract function queue($jobId, $job, $args, $jobClass, $jobParentClass, $requestTime);

}

?>