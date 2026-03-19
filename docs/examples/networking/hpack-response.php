<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\HPACK;
use Psl\IO;

// encodeWithStatus() is optimized for HTTP/2 response encoding.
// It prepends the :status pseudo-header in one pass without
// allocating an intermediate array.
$encoder = new HPACK\Encoder();
$encoded = $encoder->encodeWithStatus('200', [
    new HPACK\Header('content-type', 'application/json'),
    new HPACK\Header('cache-control', 'no-cache'),
]);

$decoder = new HPACK\Decoder();
$headers = $decoder->decode($encoded);

foreach ($headers as $header) {
    IO\write_line('%s: %s', $header->name, $header->value);
}

// Output:
// :status: 200
// content-type: application/json
// cache-control: no-cache
