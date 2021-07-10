<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl;
use Psl\Type;

use function preg_replace_callback;

/**
 * Perform a regular expression search and replace using a callback.
 * Returns the `$haystack` string with all occurrences of `$pattern` replaced using
 * `$callback`.
 *
 * @param non-empty-string $pattern The pattern to search for.
 * @param (callable(array<array-key, string>): string) $callback The replacement callable.
 * @param null|positive-int $limit The maximum possible replacements for
 *                                 $pattern within $haystack.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 * @throws Psl\Exception\InvariantViolationException If $limit is negative.
 */
function replace_with(string $haystack, string $pattern, callable $callback, ?int $limit = null): string
{
    Psl\invariant(null === $limit || $limit >= 1, '$limit must be a positive integer.');
    $limit ??= -1;

    /**
     * @psalm-suppress InvalidArgument - callable is not "pure", but we don't mind this
     * as this function itself is not pure.
     */
    $result = Internal\call_preg(
        'preg_replace_callback',
        static fn() => preg_replace_callback($pattern, $callback, $haystack, $limit),
    );

    // @codeCoverageIgnoreStart
    try {
        /**
         * @psalm-suppress ImpureFunctionCall - see #130
         * @psalm-suppress ImpureMethodCall -see #130
         */
        return Type\string()->assert($result);
    } catch (Type\Exception\AssertException $e) {
        throw new Exception\RuntimeException('Unexpected error', 0, $e);
    }
    // @codeCoverageIgnoreEnd
}
