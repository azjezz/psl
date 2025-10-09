<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns the string with the given suffix removed, or the string itself if
 * it doesn't end with the suffix.
 *
 * @pure
 */
function strip_suffix(string $string, string $suffix): string
{
    if ('' === $string || '' === $suffix) {
        return $string;
    }

    if ($string === $suffix) {
        return '';
    }

    $suffix_length = length($suffix);
    $string_length = length($string);

    // if $suffix_length is greater than $string_length, return $string as it can't contain $suffix.
    // if $suffix_length and $string_length are the same, return $string as $suffix is not $string.
    $length = $string_length - $suffix_length;
    if ($length <= 0) {
        return $string;
    }

    if (!ends_with($string, $suffix)) {
        return $string;
    }

    return slice($string, 0, $length);
}
