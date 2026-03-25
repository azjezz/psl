<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\URL;

$client = new Client\Client();

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com/large-file.tar.gz'));

$tx = $client->send(
    $request,
    new Client\SendConfiguration(maxResponseBodySize: 500_000_000, protocolVersions: [ProtocolVersion::V11]),
);

$tx->response->status;
$tx->response->body?->readAll();
