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
 * @throws Psl\Exception\InvariantViolationException if $needle is not a valid UTF-8 string.
 */
function replace_ci(string $haystack, string $needle, string $replacement, Encoding $encoding = Encoding::UTF_8): string
{
    if ('' === $needle || null === search_ci($haystack, $needle, 0, $encoding)) {
        return $haystack;
    }

    Psl\invariant(is_utf8($needle), 'Expected $needle to be a valid UTF-8 string.');

    return join(preg_split('{' . preg_quote($needle, '/') . '}iu', $haystack), $replacement);
}
