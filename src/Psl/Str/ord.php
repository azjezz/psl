<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Internal;

use function mb_ord;

/**
 * Return ASCII value of character.
 *
 * Example:
 *
 *      Str\ord('H')
 *      => Int(72)
 *
 *      Str\ord('ل')
 *      => Int(1604)
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function ord(string $char, ?string $encoding = null): int
{
    return mb_ord($char, Internal\internal_encoding($encoding));
}
