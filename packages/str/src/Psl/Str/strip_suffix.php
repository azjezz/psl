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

    $suffixLength = namespace\length($suffix, $encoding);
    $stringLength = namespace\length($string, $encoding);
    // if $suffixLength is greater than $stringLength, return $string as it can't contain $suffix.
    // if $suffixLength and $stringLength are the same, return $string as $suffix is not $string.
    $length = $stringLength - $suffixLength;
    if ($length < 0) {
        return $string;
    }

    if (!namespace\ends_with($string, $suffix, $encoding)) {
        return $string;
    }

    return namespace\slice($string, 0, $length, $encoding);
}
