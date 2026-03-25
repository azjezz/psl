<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\HTTP\Message;
use Psl\IO;
use Psl\URL;

/** @var Async\Deferred<Message\FieldMap> $deferred */
$deferred = new Async\Deferred();
$request = new Message\Request(
    method: Message\METHOD_POST,
    url: URL\parse('https://example.com/upload'),
    headers: Message\FieldMap::from([
        ['Trailer', 'Checksum'],
    ]),
    body: new IO\MemoryHandle('file contents here'),
    trailers: $deferred->getAwaitable(),
);

$deferred->complete(Message\FieldMap::from([
    ['Checksum', 'sha256=abc123...'],
]));

$trailerMap = $request->trailers?->await();
$trailerMap?->get('Checksum'); // "sha256=abc123..."
