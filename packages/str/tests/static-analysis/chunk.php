<?php

declare(strict_types=1);

namespace Psl\Str\Tests\StaticAnalysis;

use Psl;
use Psl\Str;

/** @return non-empty-string */
function return_nonempty_string(): string
{
    return 'hello';
}

/** @param non-empty-list<non-empty-string> $_ */
function take_non_empty_string_list(array $_): void {}

/** @return non-empty-lowercase-string */
function return_nonempty_lowercase_string(): string
{
    return 'hello';
}

/** @param non-empty-list<non-empty-lowercase-string> $_ */
function take_non_empty_lowercase_string_list(array $_): void {}

/**
 * @throws Psl\Exception\InvariantViolationException
 */
function test(): void
{
    take_non_empty_string_list(Str\chunk(return_nonempty_string()));

    take_non_empty_lowercase_string_list(Str\chunk(return_nonempty_lowercase_string()));

    take_non_empty_string_list(Str\Byte\chunk(return_nonempty_string()));

    take_non_empty_lowercase_string_list(Str\Byte\chunk(return_nonempty_lowercase_string()));
}
