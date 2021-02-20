<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl\Type;

use function preg_replace;

/**
 * Returns the '$haystack' string with all occurrences of `$pattern` replaced by
 * `$replacement`.
 *
 * @param non-empty-string  $pattern    The pattern to search for.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 *
 * @psalm-pure
 */
function replace(string $haystack, string $pattern, string $replacement): string
{
    $result = Internal\call_preg(
        'preg_replace',
        static fn() => preg_replace($pattern, $replacement, $haystack),
    );

    // @codeCoverageIgnoreStart
    try {
        /**
         * @psalm-suppress ImpureFunctionCall - see #130
         * @psalm-suppress ImpureMethodCall -see #130
         */
        return Type\string()->assert($result);
    } catch (Type\Exception\AssertException $e) {
        throw new Exception\RuntimeException('Unexpected error', 0, $e);
    }
    // @codeCoverageIgnoreEnd
}
