<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Calculate the metaphone key of a string.
 *
 * @param int $phonemes This parameter restricts the returned metaphone key to phonemes characters in length.
 *                      The default value of 0 means no restriction.
 *
 * @return string|null the metaphone key as a string, or NULL on failure
 */
function metaphone(string $str, int $phonemes = 0): ?string
{
    $result = \metaphone($str, $phonemes);

    return false === $result ? null : $result;
}
