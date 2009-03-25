<?php

class Resqee_Persistence_SearchParams
{
    /**
     * Error state to query on.
     *
     * The options should be:
     *  NULL  => don't care
     *  TRUE  => find jobs w/ errors
     *  FALSE => find jobs w/o errors
     *
     * @var bool
     */
    public $hasErrors = null;

    /**
     * The job status to query on
     *
     * The options should be
     *  Resqee_Persistence_Item::STATUS_QUEUED   => get queued jobs
     *  Resqee_Persistence_Item::STATUS_COMPLETE => get completed jobs
     *  Resqee_Persistence_Item::STATUS_FAILED   => get jobs that had exceptions
     *
     * @var int
     */
    public $status = null;

    /**
     * Determine if we're looking for jobs that wrote to stdout
     *
     * The options should be:
     *  NULL  => don't care
     *  TRUE  => find jobs w/ errors
     *  FALSE => find jobs w/o errors
     *
     * @var bool
     */
    public $hasStdout = null;

    /**
     * The class of jobs we're looking for
     *
     * NULL          => any job
     * SomeClassName => jobs w/ class of SomeClassName
     *
     * NOTE: if $isSearchParentClass == true then your persistence code should
     * search for jobs where the parent class or class is named $jobClass
     *
     * @var sting
     */
    public $jobClass = null;

    /**
     * Whether to expand searching by $jobClass to the job's parent class' name
     * as well
     *
     * @var bool
     */
    public $isSearchParentClass = true;

    /**
     * The minimum bound on requestTime to query on (in unixtime)
     *
     * ex: get jobs that were requested after 2pm on 8/1/2009
     *
     * NULL => no minimum time bound on requestTime
     *
     * @var int
     */
    public $minRequestTime = null;

    /**
     * The maximum bound on requestTime to query on (in unixtime)
     *
     * ex: get jobs that were requested before 12am on 9/16/2009
     *
     * NULL => no maximum time bound on requestTime
     *
     * @var int
     */
    public $maxRequestTime = null;

    /**
     * The minimum bound on responseTime to query on (in unixtime)
     *
     * ex: get jobs that were responseed after 2pm on 8/1/2009
     *
     * NULL => no minimum time bound on responseTime
     *
     * @var int
     */
    public $minResponseTime = null;

    /**
     * The maximum bound on responseTime to query on (in unixtime)
     *
     * ex: get jobs that were responseed before 12am on 9/16/2009
     *
     * NULL => no maximum time bound on responseTime
     *
     * @var int
     */
    public $maxResponseTime = null;

    /**
     * The offset to fetch jobs at
     *
     * @var int
     */
    public $offset = 0;

    /**
     * The # of jobs to fetch
     *
     * @var int
     */
    public $limit = 50;
}

?>