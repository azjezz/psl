<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

use function preg_quote;
use function preg_split;

/**
 * Returns the '$haystack' string with all occurrences of `$needle` replaced by
 * `$replacement` (case-insensitive).
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function replace_ci(string $haystack, string $needle, string $replacement, ?string $encoding = null): string
{
    if ('' === $needle || null === search_ci($haystack, $needle, 0, $encoding)) {
        return $haystack;
    }

    return join(preg_split('{' . preg_quote($needle, '/') . '}iu', $haystack), $replacement);
}
