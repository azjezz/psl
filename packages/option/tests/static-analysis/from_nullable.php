<?php

declare(strict_types=1);

namespace Psl\Option\Tests\StaticAnalysis;

use Psl\Option\Option;

use function Psl\Option\from_nullable;

/**
 * @return Option<string>
 */
function test_some(): Option
{
    return from_nullable('hello');
}

/**
 * @return Option<null>
 */
function test_none(): Option
{
    return from_nullable(null);
}

/**
 * @template T
 *
 * @param T|null $param
 *
 * @return Option<T>
 */
function test_generic(mixed $param): Option
{
    return from_nullable($param);
}

/**
 * @return Option<string>
 */
function test_some_generic(): Option
{
    return namespace\test_generic('some');
}

/**
 * @return Option<null>
 */
function test_none_generic(): Option
{
    return namespace\test_generic(null);
}

/**
 * @param string|null $x
 *
 * @return Option<string>
 */
function test_all_posibilities_generic(string|null $x): Option
{
    return namespace\test_generic($x);
}
