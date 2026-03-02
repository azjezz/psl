<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime\Duration;
use Psl\Socks;
use Psl\TCP;

// Note: This example requires a running SOCKS5 proxy server.
$proxy = new Socks\Connector(
    'proxy.example.com',
    1080,
    connector: new TCP\RetryConnector(new TCP\Connector(), maxAttempts: 3, backoff: Duration::seconds(1)),
);

$stream = $proxy->connect('target.example.com', 80);
