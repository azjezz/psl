<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TLS;

// One-step TLS connection
$tls = TLS\connect('example.com', 443);

$tls->writeAll("GET / HTTP/1.0\r\nHost: example.com\r\n\r\n");
$tls->shutdown();
$response = $tls->readAll();
$tls->close();
