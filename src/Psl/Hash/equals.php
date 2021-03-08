<?php

declare(strict_types=1);

namespace Psl\Hash;

use function hash_equals;

/**
 * Timing attack safe string comparison.
 *
 * @pure
 */
function equals(string $known_string, string $user_string): bool
{
    return hash_equals($known_string, $user_string);
}
