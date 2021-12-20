<?php

declare(strict_types=1);

namespace Psl\Str;

use function preg_quote;
use function preg_split;

/**
 * Returns the '$haystack' string with all occurrences of `$needle` replaced by
 * `$replacement` (case-insensitive).
 *
 * @pure
 */
function replace_ci(string $haystack, string $needle, string $replacement, Encoding $encoding = Encoding::UTF_8): string
{
    if ('' === $needle || null === search_ci($haystack, $needle, 0, $encoding)) {
        return $haystack;
    }

    return join(preg_split('{' . preg_quote($needle, '/') . '}iu', $haystack), $replacement);
}
