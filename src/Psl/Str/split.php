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
 * @psalm-return list<string>
 */
function split(string $string, string $delimiter, ?int $limit = null): array
{
    Psl\invariant(null === $limit || $limit >= 1, 'Expected positive limit');
    if ('' === $delimiter) {
        if (null === $limit || $limit >= length($string)) {
            return chunk($string);
        }

        if (1 === $limit) {
            return [$string];
        }

        $result = chunk(slice($string, 0, $limit - 1));
        $result[] = slice($string, $limit - 1);

        return $result;
    }

    $limit ??= Math\INT64_MAX;

    $tail = $string;
    $chunks = [];

    $position = search($tail, $delimiter);
    while (1 < $limit && null !== $position) {
        $result = slice($tail, 0, $position);
        $chunks[] = $result;
        $tail = slice($tail, length($result) + length($delimiter));

        $limit--;
        $position = search($tail, $delimiter);
    }

    $chunks[] = $tail;

    return $chunks;
}
