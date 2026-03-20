<?php

declare(strict_types=1);

namespace Psl\Str\Tests\StaticAnalysis;

use Psl;
use Psl\Str;

/** @param non-empty-lowercase-string $_ */
function take_non_empty_lowercase_string(string $_): void {}

/** @param lowercase-string $_ */
function take_lowercase_string(string $_): void {}

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
    namespace\take_lowercase_string(Str\lowercase('hello'));

    namespace\take_lowercase_string(Str\Byte\lowercase('hello'));

    namespace\take_non_empty_lowercase_string(Str\lowercase(namespace\return_non_empty_string()));

    namespace\take_non_empty_lowercase_string(Str\lowercase(namespace\return_non_falsy_string()));

    namespace\take_non_empty_lowercase_string(Str\Byte\lowercase(namespace\return_non_empty_string()));

    namespace\take_non_empty_lowercase_string(Str\Byte\lowercase(namespace\return_non_falsy_string()));
}
