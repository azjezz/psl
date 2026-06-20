<?php

declare(strict_types=1);

namespace Psl\Option\Tests\StaticAnalysis;

use Psl\Option;

/**
 * @return Option\Option<array{never, int}>
 */
function test_partial_none_tuple_1(): Option\Option<array>
{
    return Option\none()->zip::<int>(Option\some::<int>(1));
}

/**
 * @return Option\Option<array{int, never}>
 */
function test_partial_none_tuple_2(): Option\Option<array>
{
    return Option\some::<int>(1)->zip::<never>(Option\none());
}

/**
 * @return array{Option\Option<never>, Option\Option<int>}
 */
function test_partial_none_unzip_1(): array
{
    return namespace\test_partial_none_tuple_1()->unzip::<never, int>();
}

/**
 * @return Option\Option<array{int, string}>
 */
function test_some_zip(): Option\Option<array>
{
    return Option\some::<int>(1)->zip::<string>(Option\some::<string>('2'));
}

/**
 * @return array{Option\Option<int>, Option\Option<never>}
 */
function test_partial_none_unzip_2(): array
{
    return namespace\test_partial_none_tuple_2()->unzip::<int, never>();
}

/**
 * @return array{Option\Option<int>, Option\Option<string>}
 */
function test_some_unzip(): array
{
    return namespace\test_some_zip()->unzip::<int, string>();
}

/**
 * @return Option\Option<int>
 */
function test_some_zip_with(): Option\Option<int>
{
    return Option\some::<int>(1)->zipWith::<string, int>(Option\some::<string>('2'), static fn(int $a, string $b): int => $a + (int) $b);
}

/**
 * @return Option\Option<string>
 */
function test_some_zip_with_2(): Option\Option<string>
{
    return Option\some::<int>(1)->zipWith::<string, string>(Option\some::<string>('2'), static fn(int $_, string $b): string => $b);
}
