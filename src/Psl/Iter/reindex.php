<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Re-indexes an iterable by applying a function to all its values and
 * using the returned value as the new key/index.
 *
 * The function is passed the current iterable value and should return a new
 * key for that element. The value is left as-is. The original key is not passed
 * to the mapping function.
 *
 * Examples:
 *
 *     $users = [
 *         ['id' => 42, 'name' => 'foo'],
 *         ['id' => 24, 'name' => 'bar']
 *     ];
 *
 *     Iter\reindex($users, fn($user) => $user['id'])
 *     => Iter(
 *         42 => ['id' => 42, 'name' => 'foo'],
 *         24 => ['id' => 24, 'name' => 'bar']
 *     )
 *
 * @template Tk1
 * @template Tk2
 * @template Tv
 *
 * @param iterable<Tk1, Tv> $iterable Iterable to reindex
 * @param (callable(Tv): Tk2) $function
 *
 * @return Iterator<Tk2, Tv>
 *
 * @deprecated use `Dict\reindex` instead.
 * @see Dict\reindex()
 */
function reindex(iterable $iterable, callable $function): Iterator
{
    return Iterator::from(static function () use ($iterable, $function): Generator {
        foreach ($iterable as $value) {
            yield $function($value) => $value;
        }
    });
}
