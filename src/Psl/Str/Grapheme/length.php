<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

use function grapheme_strlen;

/**
 * Returns the length of the given string in grapheme units.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If unable to convert $string to UTF-16,
 *                                                   or split it into graphemes.
 */
function length(string $string): int
{
    $length = grapheme_strlen($string);

    Psl\invariant(null !== $length, 'unable to convert $string to UTF-16');
    Psl\invariant(false !== $length, 'unable to split $string into graphemes');

    return $length;
}
