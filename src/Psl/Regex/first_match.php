<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl\Type;

use function preg_match;

/**
 * Determine if $subject matches the given $pattern and return the first matches.
 *
 * @template T of array|null
 *
 * @param non-empty-string $pattern The pattern to match against.
 * @param ?Type\TypeInterface<T> $capture_groups What shape does the matching items have?
 *
 * @throws Exception\RuntimeException If an internal error accord.
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 *
 * @return (T is null ? array<array-key, string> : T)|null
 */
function first_match(
    string $subject,
    string $pattern,
    ?Type\TypeInterface $capture_groups = null,
    int $offset = 0
): ?array {
    $matching = Internal\call_preg(
        'preg_match',
        static function () use ($subject, $pattern, $offset): ?array {
            $matching = [];
            $matches  = preg_match($pattern, $subject, $matching, 0, $offset);

            return $matches === 0 ? null : $matching;
        }
    );

    if ($matching === null) {
        return null;
    }

    $capture_groups ??= Type\dict(Type\array_key(), Type\string());

    try {
        return $capture_groups->coerce($matching);
    } catch (Type\Exception\CoercionException $e) {
        throw new Exception\RuntimeException('Invalid capture groups', 0, $e);
    }
}
