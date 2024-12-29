<?php

declare(strict_types=1);

use Psl\Option;
use Psl\Type;

/**
 * @return Option\Option<array{never, int}>
 */
function test_partial_none_tuple_1(): Option\Option
{
    return Option\none()->zip(Option\some(1));
}

/**
 * @return Option\Option<array{int, never}>
 */
function test_partial_none_tuple_2(): Option\Option
{
    return Option\some(1)->zip(Option\none());
}

/**
 * @throws Type\Exception\AssertException
 *
 * @return array{Option\Option<never>, Option\Option<int>}
 */
function test_partial_none_unzip_1(): array
{
    return test_partial_none_tuple_1()->unzip();
}

/**
 * @return Option\Option<array{int, string}>
 */
function test_some_zip(): Option\Option
{
    return Option\some(1)->zip(Option\some('2'));
}

/**
 * @throws Type\Exception\AssertException
 *
 * @return array{Option\Option<int>, Option\Option<never>}
 */
function test_partial_none_unzip_2(): array
{
    return test_partial_none_tuple_2()->unzip();
}

/**
 * @throws Type\Exception\AssertException
 *
 * @return array{Option\Option<int>, Option\Option<string>}
 */
function test_some_unzip(): array
{
    return test_some_zip()->unzip();
}

/**
 * @return Option\Option<int>
 */
function test_some_zip_with()
{
    return Option\some(1)->zipWith(Option\some('2'), static fn($a, $b) => $a + ((int) $b));
}

/**
 * @return Option\Option<string>
 */
function test_some_zip_with_2()
{
    return Option\some(1)->zipWith(Option\some('2'), static fn($_a, $b) => $b);
}
