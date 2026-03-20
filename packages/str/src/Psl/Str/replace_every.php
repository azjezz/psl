<?php

declare(strict_types=1);

namespace Psl\Str;

use function array_keys;
use function array_values;
use function str_replace;

/**
 * Returns the '$haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values.
 *
 * @param array<string, string> $replacements
 *
 * @pure
 */
function replace_every(string $haystack, array $replacements, Encoding $encoding = Encoding::Utf8): string
{
    if ($encoding === Encoding::Ascii || $encoding === Encoding::Utf8) {
        return str_replace(array_keys($replacements), array_values($replacements), $haystack);
    }

    foreach ($replacements as $needle => $replacement) {
        $haystack = namespace\replace($haystack, $needle, $replacement, $encoding);
    }

    return $haystack;
}
