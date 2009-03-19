<?php

class Resqee_Controller_Index extends Resqee_Controller
{
    /**
     * Action to handle the / page
     *
     */
    public function index()
    {
        $this->render();
    }

    /**
     * Parse the passed in uri and set member variables based on the uri.
     *
     * @param string $uri A URI
     */
    protected function parseUri($uri)
    {
        $this->action   = 'index';
        $this->basePath = 'index';
    }
}

?>