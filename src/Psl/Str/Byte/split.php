<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl;
use Psl\Arr;

/**
 * Returns an array containing the string split on the given delimiter. The vec
 * will not contain the delimiter itself.
 *
 * If the limit is provided, the array will only contain that many elements, where
 * the last element is the remainder of the string.
 *
 * @psalm-return array<int, string>
 */
function split(string $string, string $delimiter, ?int $limit = null): array
{
    if ('' === $delimiter) {
        if (null === $limit || $limit >= length($string)) {
            /** @var array<int, string> $result */
            $result = chunk($string);

            return $result;
        }

        if (1 === $limit) {
            return [$string];
        }

        Psl\invariant($limit > 1, 'Expected positive limit.');
        /** @var array<int, string> $result */
        $result = chunk(slice($string, 0, $limit - 1));
        $result[] = slice($string, $limit - 1);

        return $result;
    }

    if (null === $limit) {
        /** @var array<int, string> $result */
        $result = \explode($delimiter, $string);
    } else {
        /** @var array<int, string> $result */
        $result = \explode($delimiter, $string, $limit);
    }

    Psl\invariant(Arr\is_array($result), 'Unexpected error');

    /* @var array<int, string> $result */
    return $result;
}
