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
 * @throws Psl\Exception\InvariantViolationException If $limit is negative.
 *
 * @return list<string>
 *
 * @pure
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
        /** @var list<string>|false $result */
        $result = explode($delimiter, $string);
    } else {
        /** @var list<string>|false $result */
        $result = explode($delimiter, $string, $limit);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock - should not throw
     * @psalm-suppress ImpureFunctionCall - see https://github.com/azjezz/psl/issues/130
     * @psalm-suppress ImpureMethodCall - see https://github.com/azjezz/psl/issues/130
     */
    return Type\vec(Type\string())->coerce($result);
}
