<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

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
 *     Dict\reindex($users, fn($user) => $user['id'])
 *     => Dict(
 *         42 => ['id' => 42, 'name' => 'foo'],
 *         24 => ['id' => 24, 'name' => 'bar']
 *     )
 *
 * @template Tk1
 * @template Tk2 of array-key
 * @template Tv
 *
 * @param iterable<Tk1, Tv> $iterable Iterable to reindex
 * @param (Closure(Tv): Tk2) $function
 *
 * @return array<Tk2, Tv>
 */
function reindex(iterable $iterable, Closure $function): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[$function($value)] = $value;
    }

    return $result;
}
