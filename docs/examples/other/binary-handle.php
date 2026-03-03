<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Binary\Endianness;
use Psl\Binary\HandleReader;
use Psl\Binary\HandleWriter;
use Psl\IO;
use Psl\IO\MemoryHandle;

// HandleWriter writes directly to any IO\WriteHandleInterface.
// In production, this could be a TCP socket - here we use MemoryHandle for demo.
$handle = new MemoryHandle();

$writer = new HandleWriter($handle, Endianness::Big);
// version
$writer->u8(1);
// type
$writer->u16(0x0042);
// payload length
$writer->u32(5);
// payload
$writer->bytes('Hello');

IO\write_line('Wrote %d bytes to handle', strlen($handle->getBuffer()));

// Seek back to start so we can read what was written
$handle->seek(0);

// HandleReader reads directly from any IO\ReadHandleInterface
$reader = new HandleReader($handle, Endianness::Big);

$version = $reader->u8();
$type = $reader->u16();
$length = $reader->u32();
$payload = $reader->bytes($length);

IO\write_line('Version: %d', $version); // 1
IO\write_line('Type: 0x%04X', $type); // 0x0042
IO\write_line('Length: %d', $length); // 5
IO\write_line('Payload: %s', $payload); // Hello
