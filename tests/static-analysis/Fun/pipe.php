<?php

declare(strict_types=1);

use Psl\Exception\InvariantViolationException;

use function Psl\Fun\pipe;

function test_too_few_argument_dont_matter(): int
{
    $stages = pipe(static fn(): int => 2);

    return $stages('hello');
}

/**
 * @psalm-suppress InvalidArgument
 */
function test_too_many_argument_count_issues(): int
{
    $stages = pipe(static fn(string $_x, string $_y): int => 2);
    return $stages('hello');
}

function test_variadic_and_default_params(): int
{
    $stages = pipe(static fn(int $_y, string $_x = 'hello'): float => 1.2, static fn(float ...$_items): int => 23);
    return $stages(123);
}

/**
 * This can be improved once closure generic resolution is added to psalm.
 *
 * @see https://github.com/vimeo/psalm/issues/7244
 *
 * @psalm-suppress InvalidArgument
 * @psalm-suppress NoValue - Resolves into "\Closure(never): never" because of the issue linked above.
 */
function test_empty_pipe(): string
{
    $stages = pipe();
    return $stages('hello');
}

/**
 * @psalm-suppress InvalidArgument
 */
function test_invalid_arguments(): void
{
    $stages = pipe('hello', 'world');
    $stages('hello');
}

/**
 * @psalm-suppress InvalidScalarArgument
 */
function test_invalid_return_to_input_type(): float
{
    $stages = pipe(static fn(string $_x): int => 2, static fn(string $_y): float => 1.2);
    return $stages('hello');
}

/**
 * @psalm-suppress InvalidArgument
 */
function test_invalid_input_type(): float
{
    $stages = pipe(static fn(string $_x): int => 2, static fn(int $_y): float => 1.2);
    return $stages(143);
}

/**
 * @throws InvariantViolationException
 *
 * @psalm-suppress RedundantCondition
 */
function test_output_type_is_known(): void
{
    $stages = pipe(static fn(string $_x): int => 2);

    Psl\invariant(is_int($stages('hello')), 'Expected output of int');
}

function test_first_class_callables(): int
{
    $stages = pipe($assignment = static fn(string $_x): int => 2, (static fn(): int => 2)(...));
    return $stages('hello');
}
