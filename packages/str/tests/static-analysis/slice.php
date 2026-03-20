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
function tests(): void
{
    namespace\take_lowercase_string(Str\slice(namespace\return_lowercase_string(), 3, 5));

    namespace\take_lowercase_string(Str\Byte\slice(namespace\return_lowercase_string(), 3, 5));

    namespace\take_lowercase_string(Str\Grapheme\slice(namespace\return_lowercase_string(), 3, 5));
}
