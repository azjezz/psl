<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

$emoji = "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}"; // family emoji

Str\Byte\length($emoji); // 18 (raw bytes)
Str\length($emoji); // 5  (Unicode codepoints)
Str\Grapheme\length($emoji); // 1  (one visual character)
