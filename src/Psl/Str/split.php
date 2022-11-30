<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Math;

/**
 * Returns an array containing the string split on the given delimiter. The vec
 * will not contain the delimiter itself.
 *
 * If the limit is provided, the array will only contain that many elements, where
 * the last element is the remainder of the string.
 *
 * @param null|positive-int $limit
 *
 * @return list<string>
 *
 * @pure
 */
function split(string $string, string $delimiter, ?int $limit = null, Encoding $encoding = Encoding::UTF_8): array
{
    if ('' === $delimiter) {
        if (null === $limit || $limit >= length($string, $encoding)) {
            return chunk($string, 1, $encoding);
        }

        if (1 === $limit) {
            return [$string];
        }

        $length = $limit - 1;

        $result   = chunk(slice($string, 0, $length, $encoding), 1, $encoding);
        $result[] = slice($string, $length, null, $encoding);

        return $result;
    }

    $limit ??= Math\INT64_MAX;

    $tail   = $string;
    $chunks = [];

    /**
     * $offset is within bounded.
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    $position = search($tail, $delimiter, 0, $encoding);
    while (1 < $limit && null !== $position) {
        $result   = slice($tail, 0, $position, $encoding);
        $chunks[] = $result;
        $tail     = slice($tail, length($result, $encoding) + length($delimiter, $encoding), null, $encoding);

        $limit--;
        /**
         * $offset is within bounded.
         *
         * @psalm-suppress MissingThrowsDocblock
         */
        $position = search($tail, $delimiter, encoding: $encoding);
    }

    $chunks[] = $tail;

    return $chunks;
}
