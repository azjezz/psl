<?php

declare(strict_types=1);

namespace Psl\Regex;

use function preg_split;

use const PREG_SPLIT_NO_EMPTY;

/**
 * Split $subject by $pattern.
 *
 * @param non-empty-string $pattern The pattern to split $subject by.
 * @param null|int<0, max> $limit If specified, then only substrings up to limit are
 *                                returned with the rest of the string being placed in the last substring.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 *
 * @return list<string>
 *
 * @pure
 */
function split(string $subject, string $pattern, ?int $limit = null): array
{
    /** @var list<string> */
    return Internal\call_preg(
        'preg_split',
        static fn() => preg_split($pattern, $subject, $limit ?? -1, PREG_SPLIT_NO_EMPTY),
    );
}
