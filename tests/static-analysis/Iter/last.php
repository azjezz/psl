<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Iter;

use Psl\Iter;

/** @param int $_foo */
function take_integer(int $_foo): void
{
}

/** @return non-empty-list<int> */
function return_non_empty_integer_list(): array
{
    return [1, 2, 3];
}

/** @return non-empty-array<int, int> */
function return_non_empty_integer_array(): array
{
    return [1, 2, 3];
}

/** @return array{1: int, 2: int} */
function return_non_empty_keyed_array(): array
{
    return [1 => 5, 2 => 4];
}

function test(): void
{
    take_integer(Iter\last(return_non_empty_integer_list()));

    take_integer(Iter\last_key(return_non_empty_integer_list()));

    take_integer(Iter\last(return_non_empty_integer_array()));

    take_integer(Iter\last_key(return_non_empty_integer_array()));

    take_integer(Iter\last(return_non_empty_keyed_array()));

    take_integer(Iter\last_key(return_non_empty_keyed_array()));
}
