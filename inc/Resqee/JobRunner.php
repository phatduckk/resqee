<?php

require_once 'Resqee/Response.php';

class Resqee_JobRunner
{
    /**
     * The job
     *
     * @var Resqee_Job
     */
    private $job = null;

    /**
     * The original $_SERVER
     *
     * @var array
     */
    private $serverGlobal = array();

    /**
     * The response
     *
     * @var Resqee_Response
     */
    private $response = null;

    /**
     * Constructor
     *
     * @param Resqee_Job $job The job
     */
    public function __construct(Resqee_Job $job, array $serverGlobal)
    {
        $this->job          = $job;
        $this->serverGlobal = $serverGlobal;
    }

    /**
     * Get a Resqee_Response for this job
     *
     * @return Resqee_Response The response
     */
    public function getResponse()
    {
        if ($this->response == null) {
            $this->response = $this->runJob();
        }

        return $this->response;
    }

    /**
     * Run the job here
     *
     * We turn output buffering on in order to capture any stray output
     * from your job.
     *
     * @return mixed The result of the job
     */
    private function runJob()
    {
        $start = $end = 0;

        $backtrace = $exception = $jobOutput = $result = null;

        // start output buffer
        ob_start();

        try {
            $start  = microtime(true);
            $result = $this->job->run();
            $end    = microtime(true);
        } catch (Exception $e) {
            $exception = $e;
            $backtrace = debug_backtrace();
        }

        $jobOutput = ob_get_flush();
        $response  = new Resqee_Response($this->serverGlobal);

        $response->setResult($result);
        $response->setOutput($jobOutput);
        $response->setExecTime($end - $start);

        if (isset($e)) {
            $response->setException($e);
            $response->setBacktrace($backtrace);
        }

        return $response;
    }
}

?>