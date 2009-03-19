<?php

require_once 'config.php';
require_once 'Resqee/Job.php';

$resultModes = array(
    'array', 'stdClass', 'number', 'resource', 'stderr',
    'string', 'output', 'exception', 'complex array'
);

?>


<form method="GET" action="<?= $_SERVER['PHP_SELF']?>">
    <fieldset>
        <legend>Test a job</legend>

        <p>
            what do you want the job to return?
            <select name="resultMode">
                <?php
                    foreach ($resultModes as $mode) {
                        $sel = ($mode == @$_GET['resultMode'])
                            ? 'selected="true"'
                            : null;

                        echo "<option value=\"$mode\" $sel>$mode</option>";
                    }
                ?>
            </select>
        </p>

        <p>
            force a wait: <input type="text" name="wait" value="<?= @$_GET['wait']?>" /> seconds <i>the job's run() will do a sleep</i>
        </p>

        <p>
            <?php
                $checked = (@$_GET['async'])
                    ? 'checked="true"'
                    : null;
            ?>
            <input type="checkbox" name="async" value="1" <?= $checked ?>/> run async?
        </p>

        <input type="submit" value="run it" />
    </fieldset>
</form>

<?php

if (! empty($_GET['resultMode'])) {
    $job = new TestJob();
    $job->mode = $_GET['resultMode'];
    $job->wait = (@$_GET['wait']) ? $_GET['wait'] : 0;

    if (isset($_GET['async'])) {
        p($job->fire(), "The jobs uuid");
    }

    p($job->getResult(), "Result. The job is returning: {$_GET['resultMode']}");

    p($job, "The whole job");

    $backtrace = $job->getResponse()->getBacktrace();
    if (! empty($backtrace)) {
        p($job->getResponse()->getException(), "Exception");
        p($job->getResponse()->getBacktrace(), "Backtrace");
    }

    $errors = $job->getResponse()->getErrors();
    if (! empty($errors)) {
        p($errors, "PHP errors");
    }
}

?>