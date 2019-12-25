<?php

declare(strict_types=1);

namespace Psl\Random;

use Psl;

/**
 * Returns a cryptographically secure random bytes.
 */
function bytes(int $length): string
{
    Psl\invariant($length >= 0, 'Expected positive length, got %d', $length);
    if (0 === $length) {
        return '';
    }

    return \random_bytes($length);
}
