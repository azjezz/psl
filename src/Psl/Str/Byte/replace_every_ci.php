<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function str_ireplace;

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
    /** @psalm-var list<string> $search */
    $search = [];
    /** @psalm-var list<string> $replace */
    $replace = [];
    foreach ($replacements as $k => $v) {
        $search[]  = $k;
        $replace[] = $v;
    }

    return str_ireplace($search, $replace, $haystack);
}
