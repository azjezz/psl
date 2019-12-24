<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Return information about words used in a string.
 *
 * @param string|null $charlist A list of additional characters which will be considered as 'word'
 *
 * @return array<int, string>   an associative array, where the key is the numeric position of
 *                    the word inside the string and the value is the actual word itself
 */
function words(string $string, ?string $characters_list = null): array
{
    /** @var array<int, string> $words */
    $words = null === $characters_list ? \str_word_count($string, 2) : \str_word_count($string, 2, $characters_list);

    return $words;
}
