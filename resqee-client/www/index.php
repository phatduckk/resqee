<h1>test files</h1>

<p>
    just toss files in to the <code>www</code> folder.
    make sure you require/include config.php
</p>

<ul>
    <?
        $dh = opendir('./');
        while ($file = readdir($dh)) {
            if (strpos($file, '.') !== 0) {
                echo "<li><a href=\"$file\">$file</a>";
            }
        }
    ?>
</ul>