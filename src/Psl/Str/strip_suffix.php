<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the string with the given suffix removed, or the string itself if
 * it doesn't end with the suffix.
 *
 * @pure
 */
function strip_suffix(string $string, string $suffix, Encoding $encoding = Encoding::UTF_8): string
{
    if ($string === '' || $suffix === '') {
        return $string;
    }

    if ($string === $suffix) {
        return '';
    }

    $suffix_length = length($suffix, $encoding);
    $string_length = length($string, $encoding);
    // if $suffix_length is greater than $string_length, return $string as it can't contain $suffix.
    // if $suffix_length and $string_length are the same, return $string as $suffix is not $string.
    if ($suffix_length >= $string_length) {
        return $string;
    }

    if (!ends_with($string, $suffix, $encoding)) {
        return $string;
    }

    /** @psalm-suppress InvalidArgument - $string_length is greater than $suffix_length, so the result is always int<0, max> */
    return slice($string, 0, $string_length - $suffix_length, $encoding);
}
