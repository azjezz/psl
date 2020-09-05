<?php

declare(strict_types=1);

namespace Psl\Arr;

use Closure;

/**
 * Returns an array containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Arr\filter(['', '0', 'a', 'b'])
 *      => Arr('a', 'b')
 *
 *      Arr\filter(['foo', 'bar', 'baz', 'qux'], fn(string $value): bool => Str\contains($value, 'a'));
 *      => Arr('bar', 'baz')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>                   $array
 * @psalm-param (pure-callable(Tv): bool)|null  $predicate
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-pure
 */
function filter(array $array, ?callable $predicate = null): array
{
    /** @psalm-var (pure-callable(Tv): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    $result    = [];
    foreach ($array as $k => $v) {
        if ($predicate($v)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
