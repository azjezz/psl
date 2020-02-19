<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Re-indexes an array by applying a function to all values of an iterator and
 * using the returned value as the new key/index.
 *
 * The function is passed the current iterator value and should return a new
 * key for that element. The value is left as-is. The original key is not passed
 * to the mapping function.
 *
 * Examples:
 *
 *     $users = [
 *         ['id' => 42, 'name' => 'foo'],
 *         ['id' => 24, 'name' => 'bar']
 *     ];
 *     Iter\reindex($users, fn($user) => $user['id'])
 *     => Iter(
 *         42 => ['id' => 42, 'name' => 'foo'],
 *         24 => ['id' => 24, 'name' => 'bar']
 *     )
 *
 * @psalm-template Tk1 as array-key
 * @psalm-template Tk2 as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk1, Tv>    $iterable Iterable to reindex
 * @psalm-param (callable(Tv): Tk2)   $function
 *
 * @psalm-return Generator<Tk2, Tv, mixed, void>
 */
function reindex(iterable $iterable, callable $function): Generator
{
    foreach ($iterable as $value) {
        yield $function($value) => $value;
    }
}
