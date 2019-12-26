<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl\Arr;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values (case-insensitive).
 *
 * @param iterble<string, string> $replacements
 */
function replace_every_ci(string $haystack, iterable $replacements): string
{
    $search = [];
    $replace = [];
    foreach ($replacements as $k => $v) {
        $search[] = $k;
        $replace[] = $v;
    }

    return \str_ireplace($search, $replace, $haystack);
}
