<?php

require_once 'config.php';

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

$res = socket_connect(
    $socket,
    'server.resqee.local',
    80
);

socket_set_block($socket);

if ($res === false) {
    throw new Resqee_Exception(
        socket_strerror(),
        socket_last_error()
    );
}

$data = "something=ARIN_<>?ROCKS&things=kill";
$httpData = array(
    "POST /job HTTP/1.1\r\n",
    "Host: {$jobServer["host"]}\r\n",
    "User-Agent: Resqee Client\r\n",
    "Content-Length: " . strlen($data) . "\r\n",
    "Content-Type: application/x-www-form-urlencoded\r\n",
    "Connection: close\r\n\r\n",
);

foreach ($httpData as $header) {
    socket_write($socket, "$header");
}

socket_write($socket, $data);

$r = $b = null;
while ($b = socket_read($socket, 8096)) {
    $r .= $b;
}

p($r);

$parts = explode("\r\n\r\n", $r);
array_shift($parts);
$php = implode("\r\n\r\n", $parts);

socket_close($socket);

p($php);

?>
