<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Str;

use Psl;
use Psl\Str;

/** @param lowercase-string $_foo */
function take_lowercase_string(string $_foo): void
{
}
/** @return lowercase-string */
function return_lowercase_string(): string
{
    return 'hello';
}

/**
 * @throws Psl\Exception\InvariantViolationException
 */
function test(): void
{
    take_lowercase_string(Str\splice(return_lowercase_string(), return_lowercase_string(), 0));

    take_lowercase_string(Str\Byte\splice(return_lowercase_string(), return_lowercase_string(), 0));
}
