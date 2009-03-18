<?PHP

define('RESQEE_HOST', "http://" . getenv('RESQEE_SERVER'));


?>

<fieldset>
    <legend>testing posting to <?= RESQEE_HOST ?></legend>
    <form method="POST" action="<?= RESQEE_HOST ?>/job">
        <input type="text" name="jobBlob" value="enter some crap" />
        <input type="submit" value="post job" />
    </form>
</fieldset>