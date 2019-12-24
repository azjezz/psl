<?php

declare(strict_types=1);

namespace Psl\Random;

/**
 * Returns a cryptographically secure random bytes.
 */
function bytes(int $length): string
{
    return \random_bytes($length);
}
