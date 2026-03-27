<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\URL;

// Force HTTP/1.0 for legacy server compatibility.
// Connection: close is implicit; no keep-alive, no chunked transfer encoding.
$client = new Client\Client(
    configuration: new Client\ClientConfiguration(protocolVersions: [Message\ProtocolVersion::V10]),
);

$tx = $client->send(new Message\Request(
    method: Message\METHOD_GET,
    url: URL\parse('http://legacy-server.example.com/status'),
));

$tx->response->protocolVersion; // ProtocolVersion::V10
$tx->response->status;
$tx->response->body?->readAll();
