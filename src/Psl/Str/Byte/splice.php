<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl;

use function substr_replace;

/**
 * Return the string with a slice specified by the offset/length replaced by the
 * given replacement string.
 *
 * If the length is omitted or exceeds the upper bound of the string, the
 * remainder of the string will be replaced. If the length is zero, the
 * replacement will be inserted at the offset.
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $length is negative, or $offset is out-of-bounds.
 */
function splice(string $string, string $replacement, int $offset, ?int $length = null): string
{
    Psl\invariant(null === $length || $length >= 0, 'Expected a non-negative length.');
    $offset = Psl\Internal\validate_offset($offset, length($string));

    return null === $length
        ? substr_replace($string, $replacement, $offset)
        : substr_replace($string, $replacement, $offset, $length);
}
