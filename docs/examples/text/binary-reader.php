<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Binary\Endianness;
use Psl\Binary\Reader;
use Psl\Binary\Writer;
use Psl\IO;

// Build a message, then parse it
$data = new Writer(endianness: Endianness::Big)
    ->u8(1) // version
    ->u16(0x0042) // type
    ->u32(5) // payload length
    ->bytes('Hello') // payload
    ->toString();

$reader = new Reader($data, Endianness::Big);

$version = $reader->u8();
$type = $reader->u16();
$length = $reader->u32();
$payload = $reader->bytes($length);

IO\write_line('Version: %d', $version); // 1
IO\write_line('Type: 0x%04X', $type); // 0x0042
IO\write_line('Length: %d', $length); // 5
IO\write_line('Payload: %s', $payload); // Hello

// Track cursor position
IO\write_line('Cursor: %d / %d', $reader->cursor(), $reader->length()); // 12 / 12
IO\write_line('Remaining: %d', $reader->remaining()); // 0
IO\write_line('Consumed: %s', $reader->isConsumed() ? 'yes' : 'no'); // yes
