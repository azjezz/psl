<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values (case-insensitive).
 *
 * @param array<string, string> $replacements
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function replace_every_ci(string $haystack, array $replacements, ?string $encoding = null): string
{
    foreach ($replacements as $needle => $replacement) {
        if ('' === $needle || null === search_ci($haystack, $needle, 0, $encoding)) {
            continue;
        }

        $haystack = replace_ci($haystack, $needle, $replacement, $encoding);
    }

    return $haystack;
}
