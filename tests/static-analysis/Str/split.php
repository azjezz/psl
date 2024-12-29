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
/** @param non-empty-list<non-empty-string> $_list */
function take_non_empty_string_list(array $_list): void
{
}

/** @return non-empty-lowercase-string */
function return_nonempty_lowercase_string(): string
{
    return 'hello';
}
/** @param non-empty-list<non-empty-lowercase-string> $_list */
function take_non_empty_lowercase_string_list(array $_list): void
{
}

/**
 * @throws Psl\Exception\InvariantViolationException
 */
function test(): void
{
    take_non_empty_string_list(Str\split(return_nonempty_string(), 'x'));

    take_non_empty_lowercase_string_list(Str\split(return_nonempty_lowercase_string(), 'x'));

    take_non_empty_string_list(Str\Byte\split(return_nonempty_string(), 'x'));

    take_non_empty_lowercase_string_list(Str\Byte\split(return_nonempty_lowercase_string(), 'x'));
}
