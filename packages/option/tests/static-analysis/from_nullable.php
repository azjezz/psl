<?php

declare(strict_types=1);

namespace Psl\Option\Tests\StaticAnalysis;

use Psl\Option\Option;

/**
 * @return Option<string>
 */
function test_some(): Option<string>
{
    return Option\from_nullable::<string>('hello');
}

/**
 * @return Option<null>
 */
function test_none(): Option<null>
{
    return Option\from_nullable::<null>(null);
}

/**
 * @param null|T $param
 *
 * @return Option<T>
 */
function test_generic<T>(null|T $param): Option<T>
{
    return Option\from_nullable::<T>($param);
}

/**
 * @return Option<string>
 */
function test_some_generic(): Option<string>
{
    return namespace\test_generic::<string>('some');
}

/**
 * @return Option<null>
 */
function test_none_generic(): Option<null>
{
    return namespace\test_generic::<null>(null);
}

/**
 * @param string|null $x
 *
 * @return Option<string>
 */
function test_all_posibilities_generic(string|null $x): Option<string>
{
    return namespace\test_generic::<string>($x);
}
