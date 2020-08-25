<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values (case-insensitive).
 *
 * @psalm-param array<string, string> $replacements
 *
 * @psalm-pure
 */
function replace_every_ci(string $haystack, array $replacements): string
{
    foreach ($replacements as $needle => $replacement) {
        if ('' === $needle || null === search_ci($haystack, $needle)) {
            continue;
        }

        $haystack = replace_ci($haystack, $needle, $replacement);
    }

    return $haystack;
}
