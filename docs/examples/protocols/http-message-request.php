<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Message;
use Psl\IO;
use Psl\URL;

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com/api/users?page=1'));

$request->method; // "GET"
$request->requestTarget; // "/api/users?page=1"
$request->url?->authority->host; // "example.com"
$request->protocolVersion; // ProtocolVersion::V11

$post = new Message\Request(
    method: Message\METHOD_POST,
    url: URL\parse('https://example.com/api/users'),
    headers: Message\FieldMap::from([
        ['Content-Type', 'application/json'],
        ['Accept',       'application/json'],
    ]),
    body: new IO\MemoryHandle('{"name": "Alice"}'),
);
