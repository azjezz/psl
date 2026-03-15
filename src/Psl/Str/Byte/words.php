<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function str_word_count;

/**
 * Return information about words used in a string.
 *
 * @param string|null $charactersList A list of additional characters which will be considered as 'word'
 *
 * @return array<int, string> a dict, where the key is the numeric position of
 *                            the word inside the string and the value is the actual word itself
 *
 * @pure
 */
function words(string $string, null|string $charactersList = null): array
{
    if (null === $charactersList) {
        return str_word_count($string, 2);
    }

    return str_word_count($string, 2, $charactersList);
}
