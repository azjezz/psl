<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HPACK;
use Psl\IO;

// Sensitive headers are encoded with "never indexed" representation,
// preventing intermediary proxies from caching them in their dynamic tables.
$encoder = new HPACK\Encoder();
$encoded = $encoder->encode([
    new HPACK\Header(':method', 'POST'),
    new HPACK\Header(':path', '/login'),
    new HPACK\Header(':scheme', 'https'),
    new HPACK\Header(':authority', 'example.com'),
    new HPACK\Header('authorization', 'Bearer secret-token', sensitive: true),
    new HPACK\Header('cookie', 'session=abc123', sensitive: true),
]);

$decoder = new HPACK\Decoder();
$headers = $decoder->decode($encoded);

foreach ($headers as $header) {
    $flag = $header->sensitive ? ' [sensitive]' : '';
    IO\write_line('%s: %s%s', $header->name, $header->value, $flag);
}
