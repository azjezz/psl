<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Verify that the given string is UTF-8 encoded.
 *
 * @return bool true if the given string is UTF-8 encoded, false otherwise.
 *
 * @pure
 */
function is_utf8(string $string): bool
{
    return null !== detect_encoding($string, [Encoding::UTF_8]);
}
