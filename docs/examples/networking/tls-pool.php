<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\TCP;
use Psl\TLS;

// Create a TLS-aware connector that implements TCP\ConnectorInterface
$connector = new TLS\TCPConnector(
    new TCP\Connector(),
    new TLS\Connector(TLS\ClientConfiguration::default()->withPeerVerification(true)),
);

// Use it with a standard TCP socket pool for connection reuse
$pool = new TCP\SocketPool($connector);

// First request - establishes a new TLS connection
$stream = $pool->checkout('example.com', 443);
$stream->writeAll("GET / HTTP/1.1\r\nHost: example.com\r\nConnection: keep-alive\r\n\r\n");
$response = $stream->read();
$pool->checkin($stream);

// Second request - reuses the existing TLS connection (no new handshake)
$stream = $pool->checkout('example.com', 443);
$stream->writeAll("GET /about HTTP/1.1\r\nHost: example.com\r\nConnection: close\r\n\r\n");
$response = $stream->read();
$pool->clear($stream);

$pool->close();
