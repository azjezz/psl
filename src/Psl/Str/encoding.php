<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_detect_encoding;

/**
 * Detect the encoding of the giving string.
 *
 * @psalm-return null|string The string encoding or null if unable to detect encoding.
 *
 * @psalm-pure
 */
function encoding(string $string): ?string
{
    return mb_detect_encoding($string, null, true) ?: null;
}
