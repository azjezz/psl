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

/** @param non-empty-lowercase-string $_ */
function take_non_empty_lowercase_string(string $_): void {}

/** @param non-empty-string $_ */
function take_non_empty_string(string $_): void {}

/** @param lowercase-string $_ */
function take_lowercase_string(string $_): void {}

/** @param "hhh" $_ */
function take_triple_h_string(string $_): void {}

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
