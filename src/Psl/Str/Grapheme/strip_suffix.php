<?php

declare(strict_types=1);

namespace Psl\Str\Grapheme;

use Psl;

/**
 * Returns the string with the given suffix removed, or the string itself if
 * it doesn't end with the suffix.
 *
 * @throws Psl\Exception\InvariantViolationException If unable to convert $string, or $suffix to UTF-16,
 *                                                   or split them into graphemes.
 *
 * @pure
 */
function strip_suffix(string $string, string $suffix): string
{
    if ($string === '' || $suffix === '') {
        return $string;
    }

    if ($string === $suffix) {
        return '';
    }

    $suffix_length = length($suffix);
    $string_length = length($string);
    // if $suffix_length is greater than $string_length, return $string as it can't contain $suffix.
    // if $suffix_length and $string_length are the same, return $string as $suffix is not $string.
    if ($suffix_length >= $string_length) {
        return $string;
    }

    if (!ends_with($string, $suffix)) {
        return $string;
    }

    /**
     * $string_length is greater than $suffix_length, so the result is always int<0, max>.
     *
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress InvalidArgument
     */
    return slice($string, 0, $string_length - $suffix_length);
}
