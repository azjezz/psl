<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Str;

use Psl;
use Psl\Str;

/** @param non-empty-lowercase-string $_foo */
function take_non_empty_lowercase_string(string $_foo): void
{
}

/** @param lowercase-string $_foo */
function take_lowercase_string(string $_foo): void
{
}

/** @return non-empty-string */
function return_non_empty_string(): string
{
    return 'hello';
}

/** @return non-falsy-string */
function return_non_falsy_string(): string
{
    return 'hello';
}

/**
 * @throws Psl\Exception\InvariantViolationException
 */
function test(): void
{
    take_lowercase_string(Str\lowercase('hello'));

    take_lowercase_string(Str\Byte\lowercase('hello'));

    take_non_empty_lowercase_string(Str\lowercase(return_non_empty_string()));

    take_non_empty_lowercase_string(Str\lowercase(return_non_falsy_string()));

    take_non_empty_lowercase_string(Str\Byte\lowercase(return_non_empty_string()));

    take_non_empty_lowercase_string(Str\Byte\lowercase(return_non_falsy_string()));
}
