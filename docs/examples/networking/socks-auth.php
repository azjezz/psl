<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Socks;
use Psl\TCP;

// Note: This example requires a running SOCKS5 proxy server with authentication.
$connector = new Socks\Connector(
    new TCP\Connector(),
    new Socks\Configuration('proxy.example.com', 1080, 'user', 'pass'),
);
