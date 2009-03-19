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
        $this->serializedResult = serialize($result);
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