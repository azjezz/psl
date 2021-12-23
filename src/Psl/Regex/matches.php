<?php

declare(strict_types=1);

namespace Psl\Regex;

use function preg_match;

/**
 * Determine if $subject matches the given $pattern.
 *
 * @param non-empty-string $pattern The pattern to match against.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException If an internal error accord.
 *
 * @pure
 */
function matches(string $subject, string $pattern, int $offset = 0): bool
{
    $_ = [];
    return Internal\call_preg(
        'preg_match',
        static fn() => preg_match($pattern, $subject, $_, 0, $offset),
    ) === 1;
}
