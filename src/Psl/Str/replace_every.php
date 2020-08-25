<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the '$haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values.
 *
 * @psalm-param array<string, string> $replacements
 *
 * @psalm-pure
 */
function replace_every(string $haystack, array $replacements): string
{
    foreach ($replacements as $needle => $replacement) {
        $haystack = replace($haystack, $needle, $replacement);
    }

    return $haystack;
}
