<?php

declare(strict_types=1);

namespace Psl\Str;

use function metaphone as php_metaphone;

/**
 * Calculate the metaphone key of a string.
 *
 * @param int $phonemes This parameter restricts the returned metaphone key to phonemes characters in length.
 *                      The default value of 0 means no restriction.
 *
 * @return string|null the metaphone key as a string, or NULL on failure
 *
 * @psalm-pure
 */
function metaphone(string $string, int $phonemes = 0): ?string
{
    $result = php_metaphone($string, $phonemes);

    return false === $result ? null : $result;
}
