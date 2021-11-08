<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values (case-insensitive).
 *
 * @param array<string, string> $replacements
 *
 * @pure
 */
function replace_every_ci(string $haystack, array $replacements, Encoding $encoding = Encoding::UTF_8): string
{
    foreach ($replacements as $needle => $replacement) {
        if ('' === $needle || null === search_ci($haystack, $needle, 0, $encoding)) {
            continue;
        }

        $haystack = replace_ci($haystack, $needle, $replacement, $encoding);
    }

    return $haystack;
}
