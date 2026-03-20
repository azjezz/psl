<?php

declare(strict_types=1);

namespace Psl\Str\Tests\StaticAnalysis;

use Psl;
use Psl\Str;

/** @param lowercase-string $_ */
function take_lowercase_string(string $_): void {}

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
    namespace\take_lowercase_string(Str\splice(
        namespace\return_lowercase_string(),
        namespace\return_lowercase_string(),
        0,
    ));

    namespace\take_lowercase_string(Str\Byte\splice(
        namespace\return_lowercase_string(),
        namespace\return_lowercase_string(),
        0,
    ));
}
