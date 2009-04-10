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

    public function foo()
    {
        print_r($_POST);
        exit;
    }
}

?>