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
     * Any exception caught when run() was called on your job
     *
     * @var Exception
     */
    private $exception = null;

    /**
     * Any stray output generated in your job
     *
     * You can use this for debugging, i guess, but your job really shouldnt
     * produce any stray output
     *
     * @var string
     */
    private $stdout = null;

    /**
     * The captured output of php errors in your job.
     *
     * The error level is E_ALL
     *
     * @var array
     */
    private $errors = array();

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
    const STATUS_RESPONSE_TIME = 'responseTime';

    /**
     * const used as key in $status to represent how many seconds a job took
     *
     */
    const EXEC_MS = 'ms';

    /**
     * Result of the job as serialized PHP
     *
     * @var string
     */
    private $serializedResult = null;

    /**
     * Constructor
     *
     */
    public final function __construct($serverglobal)
    {
        $this->setResponseTime();
        $this->setRequestTime($serverglobal['REQUEST_TIME']);
    }

    /**
     * Set the request time
     *
     * @param int $unixtime
     */
    private function setRequestTime($unixtime)
    {
        $this->status[self::STATUS_REQUEST_TIME] = $unixtime;
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

        $this->status[self::STATUS_RESPONSE_TIME] = $unixtime;
    }

    /**
     * Get the array that contains status meta data
     *
     * @return array The status info
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set $status['execSeconds']
     *
     * @param float $seconds
     */
    public function setExecTime($seconds)
    {
        $this->status[self::EXEC_MS] = round($seconds * 1000, 4);
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

        // set $this->resultDataType if $result is a non-stdClass object
        if (is_object($result) && ! ($result instanceof stdClass)) {
            $this->resultDataType = get_class($result);
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
     * Set the exception if the job threw one
     *
     * @param Exception $e
     */
    public function setException(Exception $e)
    {
        $this->exception      = $e;
        $this->exceptionClass = get_class($e);
    }

    /**
     * Get the exception your job threw... if any
     *
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set $output
     *
     * @param string $output The output caught from the job
     */
    public function setStdout($output)
    {
        $this->stdout = $output;
    }

    /**
     * Add any php errors to $this->errors
     *
     * @param array $err An error message
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Get any php errors you job might have triggered
     *
     */
    public function getErrors()
    {
        return $this->errors;
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
        if ($this->resultDataType !== null) {
            Resqee::loadClass($this->resultDataType);
        }

        return unserialize($this->serializedResult);
    }
}

?>