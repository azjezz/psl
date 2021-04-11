<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Str;

use Psl;
use Psl\Str;

/** @return non-empty-string */
function return_nonempty_string(): string
{
    return 'hello';
}
/** @return non-empty-lowercase-string */
function return_nonempty_lowercase_string(): string
{
    return 'hello';
}
/** @return lowercase-string */
function return_lowercase_string(): string
{
    return 'hello';
}

/** @param non-empty-lowercase-string $_foo */
function take_non_empty_lowercase_string(string $_foo): void
{
}
/** @param non-empty-string $_foo */
function take_non_empty_string(string $_foo): void
{
}
/** @param lowercase-string $_foo */
function take_lowercase_string(string $_foo): void
{
}

/** @param "hhh" $_x */
function take_triple_h_string(string $_x): void
{
}

/**
 * @throws Psl\Exception\InvariantViolationException
 */
function test(): void
{
    take_non_empty_lowercase_string(Str\repeat(return_nonempty_lowercase_string(), 4));
    take_non_empty_string(Str\repeat(return_nonempty_string(), 4));
    take_lowercase_string(Str\repeat(return_lowercase_string(), 4));

    take_triple_h_string(Str\repeat('h', 3));
}
