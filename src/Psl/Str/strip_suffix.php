<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the string with the given suffix removed, or the string itself if
 * it doesn't end with the suffix.
 *
 * @pure
 */
function strip_suffix(string $string, string $suffix, Encoding $encoding = Encoding::Utf8): string
{
    if ('' === $string || '' === $suffix) {
        return $string;
    }

    if ($string === $suffix) {
        return '';
    }

    $suffix_length = length($suffix, $encoding);
    $string_length = length($string, $encoding);
    // if $suffix_length is greater than $string_length, return $string as it can't contain $suffix.
    // if $suffix_length and $string_length are the same, return $string as $suffix is not $string.
    $length = $string_length - $suffix_length;
    if ($length < 0) {
        return $string;
    }

    if (!ends_with($string, $suffix, $encoding)) {
        return $string;
    }

    return slice($string, 0, $length, $encoding);
}
