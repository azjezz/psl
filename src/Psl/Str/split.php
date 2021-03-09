<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;
use Psl\Math;

/**
 * Returns an array containing the string split on the given delimiter. The vec
 * will not contain the delimiter itself.
 *
 * If the limit is provided, the array will only contain that many elements, where
 * the last element is the remainder of the string.
 *
 * @throws Psl\Exception\InvariantViolationException If a negative $limit is given.
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 *
 * @return list<string>
 *
 * @pure
 */
function split(string $string, string $delimiter, ?int $limit = null, ?string $encoding = null): array
{
    Psl\invariant(null === $limit || $limit >= 1, 'Expected a non-negative limit');
    if ('' === $delimiter) {
        if (null === $limit || $limit >= length($string, $encoding)) {
            return chunk($string, 1, $encoding);
        }

        if (1 === $limit) {
            return [$string];
        }

        $result   = chunk(slice($string, 0, $limit - 1, $encoding), 1, $encoding);
        $result[] = slice($string, $limit - 1, null, $encoding);

        return $result;
    }

    $limit ??= Math\INT64_MAX;

    $tail   = $string;
    $chunks = [];

    $position = search($tail, $delimiter, 0, $encoding);
    while (1 < $limit && null !== $position) {
        $result   = slice($tail, 0, $position, $encoding);
        $chunks[] = $result;
        $tail     = slice($tail, length($result, $encoding) + length($delimiter, $encoding), null, $encoding);

        $limit--;
        $position = search($tail, $delimiter, 0, $encoding);
    }

    $chunks[] = $tail;

    return $chunks;
}
