<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl\Arr;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values (case-insensitive).
 */
function replace_every_ci(string $haystack, array $replacements): string
{
    return \str_ireplace(
        Arr\keys($replacements),
        Arr\values($replacements),
        $haystack
    );
}
