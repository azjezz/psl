<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

use function metaphone as php_metaphone;

/**
 * Calculate the metaphone key of a string.
 *
 * @param int $phonemes This parameter restricts the returned metaphone key to phonemes characters in length.
 *                      the default value of 0 means no restriction.
 *
 * @throws Psl\Exception\InvariantViolationException If $phonemes is negative.
 *
 * @return string the metaphone key as a string
 *
 * @pure
 */
function metaphone(string $string, int $phonemes = 0): string
{
    Psl\invariant($phonemes >= 0, 'Expected non-negative phonemes, got %d.', $phonemes);

    return php_metaphone($string, $phonemes);
}
