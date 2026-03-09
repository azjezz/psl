<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Binary\Endianness;
use Psl\Binary\Writer;
use Psl\IO;

// Build a binary protocol message: version(u8) + type(u16) + length(u32) + payload
// Writer::default() creates a new Writer with default settings (big-endian)
$message = Writer::default()
    ->u8(1) // protocol version
    ->u16(0x0042) // message type
    ->u32(5) // payload length
    ->bytes('Hello') // payload
    ->toString();

IO\write_line('Message hex: %s', bin2hex($message));
// 01004200000005 48656c6c6f

// The Writer is immutable -- each call returns a new instance
$base = new Writer(endianness: Endianness::Little);
$a = $base->u16(1)->u16(2);
$b = $base->u16(3)->u16(4);

IO\write_line('A: %s', bin2hex($a->toString())); // 01000200
IO\write_line('B: %s', bin2hex($b->toString())); // 03000400

// You can also mix endianness per call
$mixed = new Writer(endianness: Endianness::Big)
    ->u16(0x0102) // big-endian (default)
    ->u16(0x0304, Endianness::Little) // little-endian override
    ->toString();

IO\write_line('Mixed: %s', bin2hex($mixed)); // 01020403
