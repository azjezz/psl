<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

use function str_replace;

/**
 * Returns the 'haystack' string with all occurrences of `$needle` replaced by
 * `$replacement`.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function replace(string $haystack, string $needle, string $replacement, ?string $encoding = null): string
{
    if ('' === $needle || null === search($haystack, $needle, 0, $encoding)) {
        return $haystack;
    }

    return str_replace($needle, $replacement, $haystack);
}
