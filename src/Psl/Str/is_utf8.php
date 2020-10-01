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
function is_utf8(string $string): ?bool
{
    return ! (false === mb_detect_encoding($string, 'UTF-8', true));
}