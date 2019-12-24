<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

/**
 * Returns the 'haystack' string with all occurrences of the keys of
 * `$replacements` replaced by the corresponding values (case-insensitive).
 *
 * @psalm-param array<string, string> $replacements
 */
function replace_every_ci(string $haystack, array $replacements): string
{
    Psl\invariant('' === $haystack || \preg_match('//u', $haystack), 'Invalid UTF-8 string.');

    foreach ($replacements as $needle => $replacement) {
        Psl\invariant('' === $needle || \preg_match('//u', $needle), 'Invalid UTF-8 string.');
        Psl\invariant('' === $replacement || \preg_match('//u', $replacement), 'Invalid UTF-8 string.');

        if ('' === $needle || null === search($haystack, $needle)) {
            continue;
        }

        $haystack = replace_ci($haystack, $needle, $replacement);
    }

    return $haystack;
}
