<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HTTP\Message;
use Psl\IO;

$response = new Message\Response(
    status: Message\STATUS_OK,
    headers: Message\FieldMap::from([
        ['Content-Type', 'text/html; charset=utf-8'],
    ]),
    body: new IO\MemoryHandle('<h1>Hello</h1>'),
);

$response->status; // 200
$response->headers->get('Content-Type'); // "text/html; charset=utf-8"

Message\reason_phrase($response->status); // "OK"
Message\reason_phrase(Message\STATUS_NOT_FOUND); // "Not Found"
