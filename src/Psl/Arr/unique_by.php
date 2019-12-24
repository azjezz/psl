<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new iterable in which each value appears exactly once, where the
 * value's uniqueness is determined by transforming it to a scalar via the
 * given function. In case of duplicate scalar values, later keys will overwrite
 * the previous ones.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 * @psalm-template Ts as array-key
 *
 * @psalm-param array<Tk, Tv>       $iterable
 * @psalm-param (callable(Tv): Ts)  $scalar_func
 *
 * @psalm-return array<Tk, Tv>
 */
function unique_by(array $iterable, callable $scalar_func): array
{
    /** @psalm-var array<Tk, Tv> */
    return Iter\to_array_with_keys(Iter\pull(
        Iter\pull_with_key(
            $iterable,
            /**
             * @psalm-param Tk $k
             * @psalm-param Tv $_
             *
             * @psalm-return Tk
             */
            static function ($k, $_) {
                return $k;
            },
            /**
             * @psalm-param Tk $_
             * @psalm-param Tv $v
             *
             * @psalm-return Ts
             */
            static function ($_, $v) use ($scalar_func) {
                return $scalar_func($v);
            }
        ),
        /**
         * @psalm-param Tk $orig_key
         *
         * @psalm-return Tv
         */
        static function ($orig_key) use ($iterable) {
            /** @psalm-var Tv */
            return $iterable[$orig_key];
        },
        /**
         * @psalm-param Tk $x
         *
         * @psalm-return Tk
         */
        static function ($x) {
            return $x;
        }
    ));
}
