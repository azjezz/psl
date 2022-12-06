<?php

declare(strict_types=1);

use Psl\Exception\InvariantViolationException;

use function Psl\Fun\pipe;

function test_too_few_argument_dont_matter(): int
{
    $stages = pipe(
        static fn (): int => 2,
    );

    return $stages('hello');
}

/**
 * @psalm-suppress InvalidArgument, UnusedClosureParam
 */
function test_too_many_argument_count_issues(): int
{
    $stages = pipe(
        static fn (string $x, string $y): int => 2,
    );
    return $stages('hello');
}

/**
 * @psalm-suppress UnusedClosureParam
 */
function test_variadic_and_default_params(): int
{
    $stages = pipe(
        static fn (int $y, string $x = 'hello'): float => 1.2,
        static fn (float ...$items): int => 23
    );
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
    $stages = pipe(
        'hello',
        'world'
    );
    $stages('hello');
}

/**
 * @psalm-suppress InvalidScalarArgument, UnusedClosureParam
 */
function test_invalid_return_to_input_type(): float
{
    $stages = pipe(
        static fn (string $x): int => 2,
        static fn (string $y): float => 1.2
    );
    return $stages('hello');
}

/**
 * @psalm-suppress UnusedClosureParam, InvalidArgument
 */
function test_invalid_input_type(): float
{
    $stages = pipe(
        static fn (string $x): int => 2,
        static fn (int $y): float => 1.2
    );
    return $stages(143);
}

/**
 * @throws InvariantViolationException
 *
 * @psalm-suppress UnusedClosureParam, RedundantCondition
 */
function test_output_type_is_known(): void
{
    $stages = pipe(
        static fn (string $x): int => 2,
    );

    Psl\invariant(is_int($stages('hello')), 'Expected output of int');
}

/**
 * @psalm-suppress UnusedClosureParam
 */
function test_first_class_callables(): int
{
    $stages = pipe(
        $assignment = static fn (string $x): int => 2,
        (static fn (): int => 2)(...),
    );
    return $stages('hello');
}
