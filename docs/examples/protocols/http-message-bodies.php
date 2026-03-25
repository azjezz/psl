<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Message;
use Psl\IO;
use Psl\URL;

$request = new Message\Request(
    method: Message\METHOD_POST,
    url: URL\parse('https://example.com/upload'),
    headers: Message\FieldMap::from([
        ['Content-Type', 'application/json'],
    ]),
    body: new IO\MemoryHandle('{"name": "Alice", "role": "admin"}'),
);

$body = $request->body;
$body?->tryRead(16); // '{"name": "Alice"' (non-blocking, up to 16 bytes)
$body?->read(maxBytes: 8); // ', "role"' (suspends until data available)

$response = new Message\Response(
    status: Message\STATUS_OK,
    body: new IO\MemoryHandle('<html><body>Hello, world!</body></html>'),
);

$response->body?->readAll(); // '<html><body>Hello, world!</body></html>'
