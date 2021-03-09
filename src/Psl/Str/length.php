<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_strlen;

/**
 * Returns the length of the given string, i.e. the number of bytes.
 *
 * Example:
 *
 *      Str\length('Hello')
 *      => Int(5)
 *
 *      Str\length('سيف')
 *      => Int(3)
 *
 *      Str\length('تونس')
 *      => Int(4)
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function length(string $string, ?string $encoding = null): int
{
    return mb_strlen($string, Internal\internal_encoding($encoding));
}
