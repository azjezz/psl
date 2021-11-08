<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the '$haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values.
 *
 * @param array<string, string> $replacements
 *
 * @pure
 */
function replace_every(string $haystack, array $replacements, Encoding $encoding = Encoding::UTF_8): string
{
    foreach ($replacements as $needle => $replacement) {
        $haystack = replace($haystack, $needle, $replacement, $encoding);
    }

    return $haystack;
}
