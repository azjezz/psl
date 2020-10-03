<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_detect_encoding;

/**
 * Verify that the given string is UTF-8 encoded.
 *
 * @return bool True if the given string is UTF-8 encoded, false otherwise.
 *
 * @psalm-pure
 */
function is_utf8(string $string): bool
{
    return ! (false === mb_detect_encoding($string, 'UTF-8', true));
}
