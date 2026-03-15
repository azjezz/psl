<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\QuotedPrintable;
use Psl\IO;

$encoded = QuotedPrintable\encode("Hello =World!\r\nLine 2 with trailing space ");
IO\write_line('Encoded: %s', $encoded);

$decoded = QuotedPrintable\decode($encoded);
IO\write_line('Decoded: %s', $decoded);

// Custom line length
$short = QuotedPrintable\encode(str_repeat('A', 50), maxLineLength: 30);
IO\write_line('Short lines: %s', $short);

// Custom line ending
$unix = QuotedPrintable\encode("line1\r\nline2", lineEnding: "\n");
IO\write_line('Unix endings: %s', $unix);
