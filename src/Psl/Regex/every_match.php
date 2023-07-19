<?php

declare(strict_types=1);

namespace Psl\Regex;

use Psl\Type;

use function preg_match_all;

/**
 * Determine if $subject matches the given $pattern and return every matches.
 *
 * @template T of array|null
 *
 * @param non-empty-string $pattern The pattern to match against.
 * @param ?Type\TypeInterface<T> $capture_groups What shape does a single set of matching items have?
 *
 * @throws Exception\RuntimeException If an internal error accord.
 * @throws Exception\InvalidPatternException If $pattern is invalid.
 *
 * @return (T is null ? list<array<array-key, string>> : list<T>)|null
 */
function every_match(
    string $subject,
    string $pattern,
    ?Type\TypeInterface $capture_groups = null,
    int $offset = 0
): ?array {
    $matching = Internal\call_preg(
        'preg_match_all',
        static function () use ($subject, $pattern, $offset): ?array {
            $matching = [];
            $matches  = preg_match_all($pattern, $subject, $matching, PREG_SET_ORDER, $offset);

            return $matches === 0 ? null : $matching;
        }
    );

    if ($matching === null) {
        return null;
    }

    $capture_groups ??= Type\dict(Type\array_key(), Type\string());

    try {
        return Type\vec($capture_groups)->coerce($matching);
    } catch (Type\Exception\CoercionException $e) {
        throw new Exception\RuntimeException('Invalid capture groups', 0, $e);
    }
}
