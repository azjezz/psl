<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Return a 2-elements array for which the given predicate returned `true` and `false`, respectively.
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T>         $list
 * @psalm-param (callable(T): bool) $predicate
 *
 * @psalm-return array{0: list<T>, 1: list<T>}
 */
function partition(iterable $list, callable $predicate): array
{
    $success = [];
    $failure = [];
    foreach ($list as $value) {
        if ($predicate($value)) {
            $success[] = $value;
        } else {
            $failure[] = $value;
        }
    }

    return [$success, $failure];
}
