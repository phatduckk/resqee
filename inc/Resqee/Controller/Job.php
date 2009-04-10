<?php

require_once 'Resqee/JobRunner.php';
require_once 'Resqee/Persistence/MySQL.php';
require_once 'Resqee/Persistence/Item.php';

class Resqee_Controller_Job extends Resqee_Controller
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
        try {
            Resqee::loadClass($_POST[Resqee::KEY_POST_JOB_CLASS_PARAM]);
        } catch (Exception $e) {
            throw new Resqee_Exception("Could not load job class");
        }

        $plugins    = Resqee_Plugins::getPlugins(Resqee_Plugin_Events::SERVER_JOB_POST);
        $responses  = array();
        $serialized = stripslashes(urldecode($_POST[Resqee::KEY_POST_JOB_PARAM]));
        $jobs       = unserialize($serialized);

        foreach ($jobs as $jobId => $jobData) {
            $job = unserialize($jobData[Resqee::KEY_POST_JOB_PARAM]);

            if (! is_object($job) || ! ($job instanceof Resqee_Job)) {
                throw new Resqee_Exception(
                    "Invalid job. Job must be an instance of Resqee_Job"
                );
            }

            // don't bother unserializing nulls
            $args = ('N;' != $jobData[Resqee::KEY_POST_JOB_ARGS_PARAM])
                ? unserialize($jobData[Resqee::KEY_POST_JOB_ARGS_PARAM])
                : null;

            $item              = new Resqee_Persistence_Item();
            $item->jobId       = $jobId;
            $item->job         = $jobData[Resqee::KEY_POST_JOB_PARAM];
            $item->args        = $jobData[Resqee::KEY_POST_JOB_ARGS_PARAM];
            $item->class       = get_class($job);
            $item->parentClass = get_parent_class($job);
            $item->requestTime = $this->serverGlobal['REQUEST_TIME'];

            // run before() on each regsitered plugin
            foreach ($plugins as $plugin) {
                $plugin->before($item);
            }

            $runner = new Resqee_JobRunner(
                $job,
                $args,
                $this->serverGlobal
            );

            $responses[$jobData[Resqee::KEY_POST_JOB_ID_PARAM]] = $runner->getResponse();
        }

        // run after() for each registered plugin
        foreach ($responses as $jobId => $response) {
            foreach ($plugins as $plugin) {
                $plugin->after($jobId, $response);
            }
        }

        echo serialize($responses);
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