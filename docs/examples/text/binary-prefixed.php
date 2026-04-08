<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Binary\Reader;
use Psl\Binary\Writer;
use Psl\IO;

// Length-prefixed bytes: write the payload length as a typed integer,
// followed by the raw payload bytes, in a single method call.

$data = Writer::default()
    ->u8(1) // version
    ->u16(0x0042) // message type
    ->u32PrefixedBytes('Hello, PSL!') // u32 length prefix + payload
    ->u8PrefixedBytes('OK') // u8 length prefix + short status
    ->toString();

IO\write_line('Message hex: %s', bin2hex($data));

// Read it back
$reader = new Reader($data);

$version = $reader->u8();
$type = $reader->u16();
$payload = $reader->u32PrefixedBytes(); // reads u32 length, then that many bytes
$status = $reader->u8PrefixedBytes();

IO\write_line('Version: %d', $version); // 1
IO\write_line('Type: 0x%04X', $type); // 0x0042
IO\write_line('Payload: %s', $payload); // Hello, PSL!
IO\write_line('Status: %s', $status); // OK

// skip() advances the reader without returning the data
$data2 = Writer::default()->u32(0xDEAD)->u32PrefixedBytes('Important')->toString(); // header we want to skip

$reader2 = new Reader($data2);
$reader2->skip(4); // skip the 4-byte header
IO\write_line('Skipped to: %s', $reader2->u32PrefixedBytes()); // Important
