<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\EncodedWord;
use Psl\IO;

// Encode a non-ASCII string for use in a MIME header
$encoded = EncodedWord\encode("caf\xC3\xA9 latt\xC3\xA9 is great");
IO\write_line('Encoded: %s', $encoded);

// Decode encoded-words back to UTF-8
$decoded = EncodedWord\decode($encoded);
IO\write_line('Decoded: %s', $decoded);

// Decode a B-encoded header
$subject = EncodedWord\decode('=?utf-8?B?Y2Fmw6k=?=');
IO\write_line('Subject: %s', $subject);

// Decode a Q-encoded header with underscores as spaces
$from = EncodedWord\decode('=?utf-8?Q?John_Doe?=');
IO\write_line('From: %s', $from);

// Plain ASCII passes through unchanged
$plain = EncodedWord\encode('Hello, World!');
IO\write_line('Plain: %s', $plain);
