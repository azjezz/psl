<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Returns an array containing only the keys and values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting both they key and the value to boolean.
 *
 * Example:
 *
 *      Arr\filter_with_key(['a', '0', 'b', 'c'])
 *      => Iter('b', 'c')
 *
 *      Arr\filter_with_key(
 *          ['foo', 'bar', 'baz', 'qux'],
 *          fn(int $key, string $value): bool => $key > 1 && Str\contains($value, 'a')
 *      );
 *      => Arr('baz')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>                       $array
 * @psalm-param (pure-callable(Tk, Tv): bool)|null  $predicate
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-pure
 */
function filter_with_key(array $array, ?callable $predicate = null): array
{
    $predicate = $predicate ??
        /**
         * @psalm-param Tk $k
         * @psalm-param Tv $v
         *
         * @psalm-pure
         */
        fn ($k, $v): bool => Psl\Internal\boolean($v);

    /** @psalm-var array<Tk, Tv> $result */
    $result = [];
    foreach ($array as $k => $v) {
        if ($predicate($k, $v)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
