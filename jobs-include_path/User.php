<?php

class User
{
    private $garbage = 666;

    public function __construct($userid, $username)
    {
        $this->userid = $userid;
        $this->username = $username;
    }
}

?>