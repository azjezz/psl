<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values.
 *
 * @psalm-param iterable<string, string> $replacements
 */
function replace_every(
    string $haystack,
    iterable $replacements
): string {
    foreach ($replacements as $needle => $replacement) {
        $needle = (string) $needle;
        if ('' === $needle || null === search($haystack, $needle)) {
            continue;
        }

        $haystack = replace($haystack, $needle, $replacement);
    }

    return $haystack;
}
