<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Socks;
use Psl\TCP;
use Psl\TLS;

// Note: This example requires a running SOCKS5 proxy server.
$proxy = new Socks\Connector(new TCP\Connector(), new Socks\Configuration('proxy.example.com', 1080, 'user', 'pass'));
$stream = $proxy->connect('api.example.com', 443);
$tls = TLS\Connector::default()->connect($stream, 'api.example.com');

$tls->writeAll("GET /data HTTP/1.1\r\nHost: api.example.com\r\n\r\n");
