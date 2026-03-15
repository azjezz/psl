<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Socks;
use Psl\TCP;

// Note: This example requires a running SOCKS5 proxy server.
$connector = new Socks\Connector(new TCP\Connector(), new Socks\Configuration('proxy.example.com', 1080));
$stream = $connector->connect('target.example.com', 80);

$stream->writeAll("GET / HTTP/1.0\r\nHost: target.example.com\r\n\r\n");
$stream->shutdown();
$response = $stream->readAll();
$stream->close();
