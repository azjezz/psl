<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HPACK;
use Psl\IO;

// Encode request headers
$encoder = new HPACK\Encoder();
$encoded = $encoder->encode([
    new HPACK\Header(':method', 'GET'),
    new HPACK\Header(':path', '/'),
    new HPACK\Header(':scheme', 'https'),
    new HPACK\Header(':authority', 'example.com'),
    new HPACK\Header('accept', 'text/html'),
]);

IO\write_line('Encoded %d bytes', strlen($encoded));

// Decode on the other side
$decoder = new HPACK\Decoder();
$headers = $decoder->decode($encoded);

foreach ($headers as $header) {
    IO\write_line('%s: %s', $header->name, $header->value);
}
