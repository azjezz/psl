<?php

declare(strict_types=1);

namespace Psl\Encoding\EncodedWord\Internal;

use function mb_convert_encoding;
use function strtoupper;

/**
 * Convert decoded text from the given charset to UTF-8.
 *
 * @internal
 */
function convert_charset(string $charset, string $text): string
{
    $charset = strtoupper($charset);

    if ($charset === 'UTF-8' || $charset === 'US-ASCII' || $charset === 'ASCII') {
        return $text;
    }

    $converted = mb_convert_encoding($text, 'UTF-8', $charset);

    return $converted !== false ? $converted : $text;
}
