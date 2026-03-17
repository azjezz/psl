<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

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
function replace_every_ci(string $haystack, array $replacements): string
{
    return str_ireplace(array_keys($replacements), array_values($replacements), $haystack);
}
