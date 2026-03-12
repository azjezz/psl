<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

$handle = new IO\MemoryHandle("GET / HTTP/1.1\r\nHost: example.com\r\n\r\nbody");
$reader = new IO\Reader($handle);

// Read the request line, capped at 8192 bytes
$requestLine = $reader->readUntilBounded("\r\n", 8192);
IO\write_line('Request line: %s', $requestLine ?? '<not found>');

// Read a header line, capped at 4096 bytes
$header = $reader->readUntilBounded("\r\n", 4096);
IO\write_line('Header: %s', $header ?? '<not found>');

// If the line exceeds the limit, OverflowException is thrown:
$tinyHandle = new IO\MemoryHandle("this line is way too long\r\n");
$tinyReader = new IO\Reader($tinyHandle);

try {
    $tinyReader->readUntilBounded("\r\n", 5);
} catch (IO\Exception\OverflowException $e) {
    IO\write_line('Caught: %s', $e->getMessage());
}
