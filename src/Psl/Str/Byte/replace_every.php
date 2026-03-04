<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function array_keys;
use function array_values;
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
    return str_replace(array_keys($replacements), array_values($replacements), $haystack);
}
