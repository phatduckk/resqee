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
     * Captured errors
     *
     * @var array
     */
    private $errors = array();

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
     * Error handler that will store any errors in your job
     *
     * error_level is set to E_ALL before running your job
     *
     */
    public function handleError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $this->errors[] = array(
            'errno'      => $errno,
            'errstr'     => $errstr,
            'errfile'    => $errfile,
            'errline'    => $errline,
            'errcontext' => $errcontext
        );
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

        $currentErrorReporting = ini_get('error_reporting');
        set_error_handler(array($this, 'handleError'), E_ALL);
        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        try {
            $start  = microtime(true);
            $result = $this->job->run();
            $end    = microtime(true);
        } catch (Exception $e) {
            $exception = $e;
            $backtrace = debug_backtrace();
        }

        if (is_resource($result)) {
            trigger_error(
                "Your job returned a resource. That's useless!",
                E_USER_WARNING
            );
        }

        $jobOutput = ob_get_clean();
        $response  = new Resqee_Response($this->serverGlobal);

        $response->setResult($result);
        $response->setStdout($jobOutput);
        $response->setExecTime($end - $start);
        $response->setErrors($this->errors);

        if (isset($e)) {
            $response->setException($e);
        }

        $response->includedFiles = get_included_files();

        // restore previous error handler
        restore_error_handler();
        error_reporting($currentErrorReporting);
        ini_set('display_errors', 1);

        return $response;
    }
}

?>