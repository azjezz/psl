<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\H2;
use Psl\HPACK\Header;

/** @var H2\ClientConnectionInterface $client */
/** @var H2\ServerConnectionInterface $server */

$streamId = $client->nextStreamId();
$client->sendExtendedConnect(
    $streamId,
    protocol: 'websocket',
    scheme: 'https',
    authority: 'example.com',
    path: '/chat',
    extraHeaders: [
        new Header('origin', 'https://example.com'),
        new Header('sec-websocket-version', '13'),
    ],
);

// After the server responds with 200, the stream becomes a bidirectional
// byte tunnel. Use sendData/readEvent to exchange protocol-specific frames.
