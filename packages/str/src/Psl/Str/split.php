<?php

declare(strict_types=1);

namespace Psl\Str;

use const PHP_INT_MAX;

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
function split(string $string, string $delimiter, null|int $limit = null, Encoding $encoding = Encoding::Utf8): array
{
    if ('' === $delimiter) {
        if (null === $limit || $limit >= namespace\length($string, $encoding)) {
            return namespace\chunk($string, 1, $encoding);
        }

        if (1 === $limit) {
            return [$string];
        }

        $length = $limit - 1;

        $result = namespace\chunk(namespace\slice($string, 0, $length, $encoding), 1, $encoding);
        $result[] = namespace\slice($string, $length, null, $encoding);

        return $result;
    }

    $limit ??= PHP_INT_MAX;

    $tail = $string;
    $chunks = [];
    $delimiterLength = namespace\length($delimiter, $encoding);

    /**
     * $offset is within bounded.
     */
    $position = namespace\search($tail, $delimiter, 0, $encoding);
    while (1 < $limit && null !== $position) {
        $chunks[] = namespace\slice($tail, 0, $position, $encoding);
        $tail = namespace\slice($tail, $position + $delimiterLength, null, $encoding);

        $limit--;
        /**
         * $offset is within bounded.
         */
        $position = namespace\search($tail, $delimiter, encoding: $encoding);
    }

    $chunks[] = $tail;

    return $chunks;
}
