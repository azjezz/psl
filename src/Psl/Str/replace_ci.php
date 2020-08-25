<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the 'haystack' string with all occurrences of `$needle` replaced by
 * `$replacement` (case-insensitive).
 *
 * @psalm-pure
 */
function replace_ci(string $haystack, string $needle, string $replacement): string
{
    if ('' === $needle || null === search_ci($haystack, $needle)) {
        return $haystack;
    }

    return implode($replacement, preg_split('{' . preg_quote($needle, '/') . '}iu', $haystack));
}
