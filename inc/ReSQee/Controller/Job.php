<?php

class ReSQee_Controller_Job extends ReSQee_Controller
{
    /**
     * UUID of the job
     *
     * @var string
     */
    protected $jobUUID = null;

    /**
     * Parse the passed in uri and set member variables based on the uri.
     *
     * For POST /job we will set the action to post()
     *
     * @param string $uri A URI
     */
    protected function parseUri($uri)
    {
        $parts          = explode('/', trim($uri, '/'));
        $numParts       = count($parts);
        $this->basePath = $parts[0];

        if ($this->serverGlobal['REQUEST_METHOD'] == 'GET') {
            if ($numParts >= 2) {               // GET /job/uuid
                $this->action  = 'status';
                $this->jobUUID = $parts[1];
            } else if ($numParts == 1) {        // GET /job
                $this->action  = 'job';
            }
        } else {
            $this->action = 'post';
        }
    }

    /**
     * Handle request for /job
     *
     */
    public function job()
    {
        // redirect to jobs?
        // or maybe show info about creating a job...
    }

    /**
     * Handle requests for POST /job
     *
     * This is where we're handed a job and need to start running it
     *
     */
    public function post()
    {

    }

    /**
     * Handle requests for GET /job/<uuid>
     *
     */
    public function status()
    {
        // $this->status = Service_Job::getStatus($this->jobUUID)
        // need to figure out what a real result looks like. BS for now
        $this->status = array(
            'uuid'    => $this->jobUUID,
            'status'  => array('code' => 200, 'message' => 'OK'),
            'result'  => array(1,2,3,4)
        );

        $this->render();
    }

}

?>