<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_strtoupper;

/**
 * Returns the string with all alphabetic characters converted to uppercase.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function uppercase(string $string, ?string $encoding = null): string
{
    return mb_strtoupper($string, Internal\internal_encoding($encoding));
}
