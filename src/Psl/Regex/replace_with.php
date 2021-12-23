<?php

declare(strict_types=1);

namespace Psl\Regex;

use Closure;

use function preg_replace_callback;

/**
 * Perform a regular expression search and replace using a callback.
 * Returns the `$haystack` string with all occurrences of `$pattern` replaced using
 * `$callback`.
 *
 * @param non-empty-string $pattern The pattern to search for.
 * @param (Closure(array<array-key, string>): string) $callback The replacement closure.
 * @param null|positive-int $limit The maximum possible replacements for
 *                                 $pattern within $haystack.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 */
function replace_with(string $haystack, string $pattern, Closure $callback, ?int $limit = null): string
{
    return (string) Internal\call_preg(
        'preg_replace_callback',
        static fn() => preg_replace_callback($pattern, $callback, $haystack, $limit ?? -1),
    );
}
