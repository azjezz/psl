<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

$handle = new IO\MemoryHandle("line one\nline two\n\x00rest");
$reader = new IO\Reader($handle);

$line = $reader->readLine(); // read until newline: "line one"
$byte = $reader->readByte(); // read exactly one byte: "l"
$chunk = $reader->readFixedSize(7); // read exactly 7 bytes: "ine two"
$until = $reader->readUntil("\0"); // read until a delimiter (delimiter consumed but not returned): "\n"

IO\write_line('Line:  %s', $line ?? '<line not found>');
IO\write_line('Byte:  %s', $byte);
IO\write_line('Chunk: %s', $chunk);
IO\write_line('Until: %s', $until ?? '<suffix not found>');
