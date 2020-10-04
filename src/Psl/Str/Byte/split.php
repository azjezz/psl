<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl;
use Psl\Type;

use function explode;

/**
 * Returns an array containing the string split on the given delimiter. The vec
 * will not contain the delimiter itself.
 *
 * If the limit is provided, the array will only contain that many elements, where
 * the last element is the remainder of the string.
 *
 * @psalm-return list<string>
 *
 * @psalm-pure
 *
 * @throws Psl\Exception\InvariantViolationException If $limit is negative.
 */
function split(string $string, string $delimiter, ?int $limit = null): array
{
    if ('' === $delimiter) {
        if (null === $limit || $limit >= length($string)) {
            return chunk($string);
        }

        if (1 === $limit) {
            return [$string];
        }

        Psl\invariant($limit > 1, 'Expected a non-negative limit.');
        $result   = chunk(slice($string, 0, $limit - 1));
        $result[] = slice($string, $limit - 1);

        return $result;
    }

    if (null === $limit) {
        /** @psalm-var list<string>|false $result */
        $result = explode($delimiter, $string);
    } else {
        /** @psalm-var list<string>|false $result */
        $result = explode($delimiter, $string, $limit);
    }

    Psl\invariant(Type\is_array($result), 'Unexpected error');

    return $result;
}
