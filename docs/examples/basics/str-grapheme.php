<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;

$text = "cafe\u{0301}"; // 'cafe' + combining accent = 'caf' + visual 'é'

Str\length($text); // 5 (codepoints)
Str\Grapheme\length($text); // 4 (visual characters)

Str\Grapheme\slice($text, 0, 3); // 'caf' (3 visual characters)
