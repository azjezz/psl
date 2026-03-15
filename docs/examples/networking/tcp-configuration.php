<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\TCP;

// Configuration objects are immutable with fluent with* builder methods
$config = TCP\ListenConfiguration::default()
    ->withNoDelay(true)
    ->withBacklog(2048)
    ->withIdleConnections(128);

$listener = TCP\listen('127.0.0.1', 0, $config);

$address = $listener->getLocalAddress();
IO\write_line('Listening on %s:%d', $address->host, $address->port ?? 0);

$listener->close();

// ConnectConfiguration works the same way
$connectConfig = TCP\ConnectConfiguration::default()->withNoDelay(true);

IO\write_line('Connect config noDelay: %s', $connectConfig->noDelay ? 'true' : 'false');
