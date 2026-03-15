<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\Base64;
use Psl\IO;

// Encode: wrap a raw handle, read() returns base64-encoded output
$raw = new IO\MemoryHandle('Hello, World!');
$encoder = new Base64\EncodingReadHandle($raw);
$encoded = $encoder->readAll();
IO\write_line('Encoded: %s', $encoded);

// Decode: wrap an encoded handle, read() returns raw bytes
$encodedHandle = new IO\MemoryHandle($encoded);
$decoder = new Base64\DecodingReadHandle($encodedHandle);
$decoded = $decoder->readAll();
IO\write_line('Decoded: %s', $decoded);

// Write handles work the other direction
$output = new IO\MemoryHandle();
$encodingWriter = new Base64\EncodingWriteHandle($output);
$encodingWriter->writeAll('Binary data here');
$encodingWriter->flush();

$output->seek(0);
IO\write_line('Written encoded: %s', $output->readAll());
