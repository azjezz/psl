<?php

declare(strict_types=1);

namespace Psl\Regex;

use function array_keys;
use function array_values;
use function preg_replace;

/**
 * Returns the '$haystack' string with all occurrences of the keys of
 * '$replacements' ( patterns ) replaced by the corresponding values.
 *
 * @param array<non-empty-string, string> $replacements A dict where the keys are regular expression patterns,
 *                                                      and the values are the replacements.
 * @param null|positive-int $limit The maximum possible replacements for each pattern in $haystack.
 *
 * @throws Exception\InvalidPatternException If any of the patterns is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 *
 * @pure
 */
function replace_every(string $haystack, array $replacements, ?int $limit = null): string
{
    return (string) Internal\call_preg(
        'preg_replace',
        static fn() => preg_replace(array_keys($replacements), array_values($replacements), $haystack, $limit ?? -1),
    );
}
