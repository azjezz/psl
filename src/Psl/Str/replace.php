<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the 'haystack' string with all occurrences of `$needle` replaced by
 * `$replacement`.
 */
function replace(string $haystack, string $needle, string $replacement): string
{
    if ('' === $needle || null === search($haystack, $needle)) {
        return $haystack;
    }

    return \str_replace($needle, $replacement, $haystack);
}
