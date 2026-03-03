<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Binary;
use Psl\Binary\Endianness;
use Psl\IO;

// Encode an unsigned 16-bit integer in big-endian (network byte order)
$bytes = Binary\encode_u16(0x0102, Endianness::Big);
IO\write_line('u16 big-endian bytes: %s', bin2hex($bytes)); // 0102

// Decode it back
$value = Binary\decode_u16($bytes, Endianness::Big);
IO\write_line('Decoded u16: 0x%04X', $value); // 0x0102

// Encode a signed 32-bit integer in little-endian
$bytes = Binary\encode_i32(-1, Endianness::Little);
IO\write_line('i32 little-endian bytes: %s', bin2hex($bytes)); // ffffffff

// Decode it back
$value = Binary\decode_i32($bytes, Endianness::Little);
IO\write_line('Decoded i32: %d', $value); // -1

// Encode a 64-bit float
$bytes = Binary\encode_f64(3.14, Endianness::Big);
IO\write_line('f64 bytes: %s', bin2hex($bytes));
IO\write_line('Decoded f64: %f', Binary\decode_f64($bytes, Endianness::Big)); // 3.140000
