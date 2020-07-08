<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Return information about words used in a string.
 *
 * @param string|null $characters_list A list of additional characters which will be considered as 'word'
 *
 * @return array<int, string>   an associative array, where the key is the numeric position of
 *                    the word inside the string and the value is the actual word itself
 */
function words(string $string, ?string $characters_list = null): array
{
    if (null === $characters_list) {
        /** @var array<int, string> $words */
        $words = \str_word_count($string, 2);
    } else {
        /** @var array<int, string> $words */
        $words = \str_word_count($string, 2, $characters_list);
    }

    return $words;
}
