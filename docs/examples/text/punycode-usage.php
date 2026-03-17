<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Punycode;

// Encode Unicode to Punycode
$encoded = Punycode\encode('münchen');
// "mnchen-3ya"
IO\write_line('%s', $encoded);

// Decode Punycode to Unicode
$decoded = Punycode\decode('mnchen-3ya');
// "münchen"
IO\write_line('%s', $decoded);

// Round-trip
$encoded = Punycode\encode('日本語');
$decoded = Punycode\decode($encoded);
// "日本語"
IO\write_line('%s', $decoded);
