<?php

declare(strict_types=1);

namespace Psl\Regex;

use function preg_replace;

/**
 * Returns the '$haystack' string with all occurrences of `$pattern` replaced by
 * `$replacement`.
 *
 * @param non-empty-string $pattern The pattern to search for.
 * @param null|positive-int $limit The maximum possible replacements for
 *                                 $pattern within $haystack.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 *
 * @pure
 */
function replace(string $haystack, string $pattern, string $replacement, ?int $limit = null): string
{
    return (string) Internal\call_preg(
        'preg_replace',
        static fn() => preg_replace($pattern, $replacement, $haystack, $limit ?? -1),
    );
}
