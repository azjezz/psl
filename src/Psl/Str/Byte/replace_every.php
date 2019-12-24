<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use Psl\Arr;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values.
 *
 * @param array<string, string> $replacements
 */
function replace_every(
    string $haystack,
    array $replacements
): string {
    return \str_replace(
        Arr\keys($replacements),
        Arr\values($replacements),
        $haystack
    );
}
