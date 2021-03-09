<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * Return a 2-elements array for which the given predicate returned `true` and `false`, respectively.
 *
 * @template T
 *
 * @param iterable<T> $list
 * @param (callable(T): bool) $predicate
 *
 * @return array{0: list<T>, 1: list<T>}
 *
 * @deprecated use `Vec\partition` instead.
 * @see Vec\partition
 */
function partition(iterable $list, callable $predicate): array
{
    return Vec\partition($list, $predicate);
}
