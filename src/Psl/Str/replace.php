<?php

declare(strict_types=1);

namespace Psl\Str;

use function str_replace;

/**
 * Returns the 'haystack' string with all occurrences of `$needle` replaced by
 * `$replacement`.
 *
 * @pure
 */
function replace(string $haystack, string $needle, string $replacement, Encoding $encoding = Encoding::UTF_8): string
{
    if ('' === $needle || null === search($haystack, $needle, 0, $encoding)) {
        return $haystack;
    }

    return str_replace($needle, $replacement, $haystack);
}
