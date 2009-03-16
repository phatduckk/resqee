<?php
ob_start();

/**
 * Start up a session. We'll be using the sessionid to identify this request.
 */
session_id($_SERVER['HTTP_X_RISQ_JOB_ID']);
session_start();

// global error handler
// global exception handler
// __autoload for RiSQ_Job classes

/**
 * Dispatch the request
 */
try {
    $controller   = RiSQ_Controller::factory($_SERVER);
    $risqResponse = $controller->run();
} catch (Exception $e) {
    $risqResponse = new RiSQ_Response_Error($e);
}

echo $risqResponse->getOutput();

print_r($_SERVER);
var_dump(session_id());
var_dump(ini_get('session.save_path'));
ob_end_flush();
?>