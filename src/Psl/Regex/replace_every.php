<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl\Type;
use Psl\Vec;

use function preg_replace;

/**
 * Returns the '$haystack' string with all occurrences of the keys of
 * '$replacements' ( patterns ) replaced by the corresponding values.
 *
 * @param array<non-empty-string, string>   $replacements   A dict where the keys are regular expression patterns,
 *                                                          and the values are the replacements.
 *
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 *
 * @psalm-pure
 */
function replace_every(string $haystack, array $replacements): string
{
    /**
     * @psalm-suppress InvalidArgument - callable is not "pure", because keys() and values()
     *      are conditionally pure, in this context, we know they are.
     */
    $result = Internal\call_preg(
        'preg_replace',
        static fn() => preg_replace(Vec\keys($replacements), Vec\values($replacements), $haystack),
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
