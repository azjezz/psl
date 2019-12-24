<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Return a 2-elements array for which the given predicate returned `true` and `false`, respectively.
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T> $iterable
 * @psalm-param (callable(T): bool) $predicate
 *
 * @psalm-return array{0: array<int, T>, 1: array<int, T>}
 */
function partition(iterable $iterable, callable $predicate): array
{
    $success = [];
    $failure = [];
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            $success[] = $value;
        } else {
            $failure[] = $value;
        }
    }

    return [$success, $failure];
}
