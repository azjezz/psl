<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl;
use Psl\Type;
use Psl\Vec;

use function preg_replace;

/**
 * Returns the '$haystack' string with all occurrences of the keys of
 * '$replacements' ( patterns ) replaced by the corresponding values.
 *
 * @param array<non-empty-string, string> $replacements A dict where the keys are regular expression patterns,
 *                                                      and the values are the replacements.
 * @param null|positive-int $limit The maximum possible replacements for each pattern in $haystack.
 *
 * @throws Exception\InvalidPatternException If any of the patterns is invalid.
 * @throws Exception\RuntimeException In case of an unexpected error.
 * @throws Psl\Exception\InvariantViolationException If $limit is negative.
 *
 * @pure
 */
function replace_every(string $haystack, array $replacements, ?int $limit = null): string
{
    Psl\invariant(null === $limit || $limit >= 1, '$limit must be a positive integer.');
    $limit ??= -1;

    /**
     * @psalm-suppress InvalidArgument - callable is not "pure", because keys() and values()
     *      are conditionally pure, in this context, we know they are.
     */
    $result = Internal\call_preg(
        'preg_replace',
        static fn() => preg_replace(Vec\keys($replacements), Vec\values($replacements), $haystack, $limit),
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
