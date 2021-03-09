<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function str_replace;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values.
 *
 * @param array<string, string> $replacements
 *
 * @pure
 */
function replace_every(string $haystack, array $replacements): string
{
    $search  = [];
    $replace = [];
    foreach ($replacements as $k => $v) {
        $search[]  = $k;
        $replace[] = $v;
    }

    return str_replace($search, $replace, $haystack);
}
