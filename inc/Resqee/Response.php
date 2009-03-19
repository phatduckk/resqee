<?php

class Resqee_Response
{

    /**
     * HTTP status of a job
     *
     * @var array
     */
    private $status = array();

    /**
     * The data type of the job's result
     *
     * We need the server to tell us what time was returned in case
     * we need to require a class before unserializing the result
     *
     * A null data type means that the result is a primitive, stdClass or array
     * and we won't need to require anything
     *
     * @var string
     */
    private $resultDataType = null;

    /**
     * const for status code key in $status
     */
    const STATUS_CODE = 'code';

    /**
     * const for status code messahe in $status
     */
    const STATUS_MESSAGE = 'message';

    /**
     * const for unixtime of the request in $status
     */
    const STATUS_REQUEST_TIME = 'requestTime';

    /**
     * const for unixtime of the response in $status
     */
    const STATUS_RESPONSE_TIME = 'requestTime';

    /**
     * Result of the job as serialized PHP
     *
     * @var string
     */
    private $serializedResult = null;

    /**
     * Constructor
     *
     * @param mixed $result
     */
    public final function __construct($result = null)
    {
        $this->setResponseTime();

        if ($result) {
            $this->setResult($result);
        }
    }

    /**
     * Set the request time
     *
     * @param int $unixTime
     */
    private function setRequestTime($unixTime)
    {
        $this->status[self::STATUS_REQUEST_TIME] = $unixTime;
    }

    /**
     * Set the response time in $status
     *
     * if now $unixtime is passed in time() will be used instead
     *
     * @param int $unixtime The time as unixtime
     */
    private function setResponseTime($unixtime = null)
    {
        $unixtime = ($unixtime)
            ? $unixtime
            : time();

        $this->status[self::STATUS_RESPONSE_TIME] = $unixTime;
    }

    /**
     * Set the result of a Resqee_Job
     *
     * Pass in the job and this function will serialize it for you
     *
     * @param mixed $result The result of a Resqee_Job
     */
    public function setResult($result)
    {
        // resources don't serialize so complain if we have a resource
        if (is_resource($result)) {
            throw new ReSQee_Exception(
                "Invalid result type: Your job cannot return a resource."
            );
        }

        $this->serializedResult = serialize($result);

        // set $this->resultDataType if $result is a non-stdClass object
        if (is_object($result) && ! ($result instanceof stdClass)) {
            $this->resultDataType = gettype($result);
        }
    }

    /**
     * Get the result's data type
     *
     * A NULL data type means that we dont need to require a class file before
     * we unserialize the result
     *
     * @return string
     */
    public function getResultDataType()
    {
        return $this->resultDataType;
    }

    /**
     * Get the result
     *
     * This method will unserialize the response from the server
     *
     * @return mixed
     */
    public function getResult()
    {
        return unserialize($this->serializedResult);
    }
}

?>