i am the index page. for now we'll have random links to new functionality.

<ul>
    <li><a href="/job/<?= sha1(time()) ?>">get job status</a>
    <li><a href="/job/<?= sha1(time()) ?>.json">get job status (json output)</a>
</ul>