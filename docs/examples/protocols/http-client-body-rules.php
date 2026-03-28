<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\IO;
use Psl\URL;

$client = new Client\Client();

// TRACE requests with a body throw RequestException per RFC 9110 Section 9.3.8
try {
    $client->send(new Message\Request(
        method: Message\METHOD_TRACE,
        url: URL\parse('https://example.com'),
        body: new IO\MemoryHandle('trace body'),
    ));
} catch (Client\Exception\RequestException $e) {
    // "TRACE requests must not include a body."
    $e->getMessage();
}

// TRACE without a body is allowed
$tx = $client->send(new Message\Request(method: Message\METHOD_TRACE, url: URL\parse('https://example.com')));

// GET with a body is allowed (useful for Elasticsearch-style query bodies)
// but not recommended per RFC 9110 Section 9.3.1
$tx = $client->send(new Message\Request(
    method: Message\METHOD_GET,
    url: URL\parse('https://elasticsearch.example.com/_search'),
    headers: new Message\FieldMap([['content-type', 'application/json']]),
    body: new IO\MemoryHandle('{"query":{"match_all":{}}}'),
));

$tx->response->status;
$tx->response->body?->readAll();
