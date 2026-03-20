<?php

declare(strict_types=1);

namespace Psl\Str;

use function array_keys;
use function array_values;
use function str_ireplace;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values (case-insensitive).
 *
 * @param array<string, string> $replacements
 *
 * @pure
 */
function replace_every_ci(string $haystack, array $replacements, Encoding $encoding = Encoding::Utf8): string
{
    if ($encoding === Encoding::Ascii || $encoding === Encoding::Utf8) {
        return str_ireplace(array_keys($replacements), array_values($replacements), $haystack);
    }

    foreach ($replacements as $needle => $replacement) {
        if ('' === $needle) {
            continue;
        }

        $haystack = namespace\replace_ci($haystack, $needle, $replacement, $encoding);
    }

    return $haystack;
}
