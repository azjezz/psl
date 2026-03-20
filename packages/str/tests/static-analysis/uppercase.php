<?php

declare(strict_types=1);

namespace Psl\Str\Tests\StaticAnalysis;

use Psl;
use Psl\Str;

/** @param non-empty-string $_ */
function take_non_empty_string(string $_): void {}

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
    namespace\take_non_empty_string(Str\uppercase(namespace\return_non_empty_string()));

    namespace\take_non_empty_string(Str\Byte\uppercase(namespace\return_non_empty_string()));

    namespace\take_non_empty_string(Str\uppercase(namespace\return_non_falsy_string()));

    namespace\take_non_empty_string(Str\Byte\uppercase(namespace\return_non_falsy_string()));
}
