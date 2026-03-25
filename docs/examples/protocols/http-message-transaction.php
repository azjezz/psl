<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Message;
use Psl\IO;

$earlyHints = new Message\Response(status: Message\STATUS_EARLY_HINTS, headers: Message\FieldMap::from([
    ['Link', '</style.css>; rel=preload; as=style'],
]));

$finalResponse = new Message\Response(
    status: Message\STATUS_OK,
    headers: Message\FieldMap::from([
        ['Content-Type', 'text/html'],
    ]),
    body: new IO\MemoryHandle('<h1>Hello</h1>'),
);

$tx = new Message\Transaction(informational: [$earlyHints], pushed: null, response: $finalResponse);

$tx->response->status; // 200
$tx->informational[0]->status; // 103
$tx->informational[0]->headers->get('Link'); // "</style.css>; rel=preload; as=style"
$tx->pushed; // null (no HTTP/2 server push)
