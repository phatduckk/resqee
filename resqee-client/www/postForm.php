<?php
require_once 'config.php';
?>

<fieldset>
    <legend>testing posting to <?= RESQEE_HOST ?></legend>
    <form method="POST" action="<?= RESQEE_HOST ?>/job">
        <input type="text" name="jobBlob" value="enter some crap" />
        <input type="submit" value="post job" />
    </form>
</fieldset>