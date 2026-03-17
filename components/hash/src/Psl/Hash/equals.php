<?php

declare(strict_types=1);

namespace Psl\Hash;

use function hash_equals;

/**
 * Timing attack safe string comparison.
 *
 * @pure
 */
function equals(string $knownString, string $userString): bool
{
    return hash_equals($knownString, $userString);
}
