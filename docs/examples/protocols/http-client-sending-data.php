<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\IO;
use Psl\URL;

$client = new Client\Client();

$request = new Message\Request(
    method: Message\METHOD_POST,
    url: URL\parse('https://httpbin.org/post'),
    headers: Message\FieldMap::from([
        ['Content-Type',  'application/json'],
        ['Accept',        'application/json'],
        ['Authorization', 'Bearer tok_example'],
    ]),
    body: new IO\MemoryHandle('{"name": "Alice", "email": "alice@example.com"}'),
);

$tx = $client->send($request);

$tx->response->status;
$tx->response->body?->readAll();
