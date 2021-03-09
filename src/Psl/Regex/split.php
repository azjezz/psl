<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl;
use Psl\Type;

use function preg_split;

use const PREG_SPLIT_NO_EMPTY;

/**
 * Split $subject by $pattern.
 *
 * @param non-empty-string $pattern The pattern to split $subject by.
 * @param null|int $limit If specified, then only substrings up to limit are
 *                        returned with the rest of the string being placed in the last substring.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 * @throws Psl\Exception\InvariantViolationException If $limit is negative, or equal to 0.
 *
 * @return list<string>
 *
 * @pure
 */
function split(string $subject, string $pattern, ?int $limit = null): array
{
    Psl\invariant($limit === null || $limit > 0, '$limit must be a positive integer.');
    $result = Internal\call_preg(
        'preg_split',
        static fn() => preg_split($pattern, $subject, $limit ?? -1, PREG_SPLIT_NO_EMPTY),
    );

    // @codeCoverageIgnoreStart
    try {
        /**
         * @psalm-suppress ImpureFunctionCall - see #130
         * @psalm-suppress ImpureMethodCall -see #130
         */
        return Type\vec(Type\string())->assert($result);
    } catch (Type\Exception\AssertException $e) {
        throw new Exception\RuntimeException('Unexpected error', 0, $e);
    }
    // @codeCoverageIgnoreEnd
}
