<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Iter;

use Psl\Iter;
use Psl\Math;

/** @param positive-int $_foo */
function take_positive_integer(int $_foo): void
{
}

/** @param 0 $_foo */
function take_zero(int $_foo): void
{
}

/** @return non-empty-list<int> */
function return_non_empty_list(): array
{
    return [
        Math\maxva(1, 2, 3),
        Math\maxva(3, 4, 4),
    ];
}

/** @return non-empty-array<int, string> */
function return_non_empty_array(): array
{
    return [
        Math\maxva(1, 2, 3) => 'hello',
        Math\maxva(3, 4, 4) => 'hello',
    ];
}

/** @return array{1: 'h', 2: 'c'} */
function return_non_empty_keyed_array(): array
{
    return [1 => 'h', 2 => 'c'];
}

/** @return array<empty, empty> */
function return_array(): array
{
    return [];
}

function test(): void
{
    take_positive_integer(Iter\count(return_non_empty_array()));

    take_positive_integer(Iter\count(return_non_empty_list()));

    take_positive_integer(Iter\count(return_non_empty_keyed_array()));

    take_zero(Iter\count(return_array()));
}
