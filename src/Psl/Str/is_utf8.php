<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_detect_encoding;

/**
 * Detect the UTF-8 encoding of the giving string.
 *
 * @psalm-return bool The detected UTF-8 character encoding or FALSE if the UTF-8 encoding cannot be detected from the given string.
 *
 * @psalm-pure
 */
function is_utf8(string $string): ?bool
{
    return ! (false === mb_detect_encoding($string, 'UTF-8', true));
}
