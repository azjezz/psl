<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Socks;
use Psl\TCP;

// Note: This example requires a running SOCKS5 proxy server.
$proxy = new Socks\Connector('proxy.example.com', 1080);
$pool = new TCP\SocketPool(connector: $proxy);

$stream = $pool->checkout('api.example.com', 80);
// ... use stream ...
$pool->checkin($stream);
