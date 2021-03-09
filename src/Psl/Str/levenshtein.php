<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

use function levenshtein as php_levenshtien;

/**
 * Calculate Levenshtein distance between two strings.
 *
 * Note: In its simplest form the function will take only the two strings
 * as parameter and will calculate just the number of insert, replace and
 * delete operations needed to transform str1 into str2.
 * Note: A second variant will take three additional parameters that define
 * the cost of insert, replace and delete operations. This is more general
 * and adaptive than variant one, but not as efficient.
 *
 * @throws Psl\Exception\InvariantViolationException If neither all, or none of the costs is supplied.
 *
 * @return int this function returns the Levenshtein-Distance between the
 *             two argument strings or -1, if one of the argument strings
 *             is longer than the limit of 255 characters
 *
 * @pure
 */
function levenshtein(
    string $source,
    string $target,
    ?int $cost_of_insertion = null,
    ?int $cost_of_replacement = null,
    ?int $cost_of_deletion = null
): int {
    if (null === $cost_of_deletion && null === $cost_of_insertion && null === $cost_of_replacement) {
        return php_levenshtien($source, $target);
    }

    // https://github.com/php/php-src/blob/623911f993f39ebbe75abe2771fc89faf6b15b9b/ext/standard/levenshtein.c#L101
    Psl\invariant(
        null !== $cost_of_deletion && null !== $cost_of_insertion && null !== $cost_of_replacement,
        'Expected either all costs to be supplied, or non.'
    );

    return php_levenshtien($source, $target, $cost_of_insertion, $cost_of_replacement, $cost_of_deletion);
}
