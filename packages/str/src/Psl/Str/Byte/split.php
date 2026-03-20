<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function explode;

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
function split(string $string, string $delimiter, null|int $limit = null): array
{
    if ('' === $delimiter) {
        if (null === $limit || $limit >= namespace\length($string)) {
            return namespace\chunk($string);
        }

        if (1 === $limit) {
            return [$string];
        }

        $length = $limit - 1;

        $result = namespace\chunk(namespace\slice($string, 0, $length));
        $result[] = namespace\slice($string, $length);

        return $result;
    }

    if (null === $limit) {
        /** @var list<string> */
        return explode($delimiter, $string);
    }

    /** @var list<string> */
    return explode($delimiter, $string, $limit);
}
