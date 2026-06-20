<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\StaticAnalysis;

use Psl\Iter;
use Psl\Math;

/** @param positive-int $_ */
function take_positive_integer(int $_): void {}

/** @param 0 $_ */
function take_zero(int $_): void {}

/** @return non-empty-list<int> */
function return_non_empty_list(): array
{
    return [
        Math\maxva::<int>(1, 2, 3),
        Math\maxva::<int>(3, 4, 4),
    ];
}

/** @return non-empty-array<int, string> */
function return_non_empty_array(): array
{
    return [
        Math\maxva::<int>(1, 2, 3) => 'hello',
        Math\maxva::<int>(3, 4, 4) => 'hello',
    ];
}

/** @return array{1: 'h', 2: 'c'} */
function return_non_empty_keyed_array(): array
{
    return [1 => 'h', 2 => 'c'];
}

/** @return array{} */
function return_array(): array
{
    return [];
}

function test(): void
{
    namespace\take_positive_integer(Iter\count::<string>(namespace\return_non_empty_array()));

    namespace\take_positive_integer(Iter\count::<int>(namespace\return_non_empty_list()));

    namespace\take_positive_integer(Iter\count::<string>(namespace\return_non_empty_keyed_array()));

    namespace\take_zero(Iter\count::<mixed>(namespace\return_array()));
}
