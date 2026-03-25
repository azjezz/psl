<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Message;
use Psl\IO;
use Psl\URL;

$request = new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com/'));

$modified = $request
    ->withMethod(Message\METHOD_POST)
    ->withUrl(URL\parse('https://example.com/api/submit'))
    ->withHeader('Content-Type', 'application/json')
    ->withHeader('Authorization', 'Bearer token123')
    ->withBody(new IO\MemoryHandle('{"data": true}'))
    ->withProtocolVersion(Message\ProtocolVersion::V20);

$modified->method; // "POST"
$modified->protocolVersion; // ProtocolVersion::V20

$request->method; // "GET" (original unchanged)
