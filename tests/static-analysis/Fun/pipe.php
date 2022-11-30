<?php

declare(strict_types=1);

use Psl\Exception\InvariantViolationException;

use function Psl\Fun\pipe;

/**
 * @psalm-suppress TooFewArguments, UnusedClosureParam
 */
function test_too_few_argument_count_issues(): void
{
    $stages = pipe(
        static fn (): int => 2,
    );
    $stages('hello');
}

/**
 * @psalm-suppress TooManyArguments, UnusedClosureParam, InvalidArgument
 */
function test_too_many_argument_count_issues(): void
{
    $stages = pipe(
        static fn (string $x, string $y): int => 2,
    );
    $stages('hello');
}

/**
 * @psalm-suppress UnusedClosureParam, InvalidArgument
 */
function test_variadic_and_default_params(): void
{
    $stages = pipe(
        static fn (int $y, string $x = 'hello'): float => 1.2,
        static fn (float ...$items): int => 23
    );
    $stages('hello');
}

/**
 * This can be improved once closure generic resolution is added to psalm.
 *
 * @see https://github.com/vimeo/psalm/issues/7244
 *
 * @psalm-suppress InvalidArgument
 */
function test_empty_pipe(): void
{
    $stages = pipe();
    $stages('hello');
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
 * @psalm-suppress UnusedClosureParam, InvalidArgument
 */
function test_invalid_return_to_input_type(): void
{
    $stages = pipe(
        static fn (string $x): int => 2,
        static fn (string $y): float => 1.2
    );
    $stages('hello');
}

/**
 * @psalm-suppress UnusedClosureParam, InvalidArgument
 */
function test_invalid_input_type(): void
{
    $stages = pipe(
        static fn (string $x): int => 2,
        static fn (int $y): float => 1.2
    );
    $stages(143);
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
function test_currently_unparsed_input_types(): void
{
    $stages = pipe(
        $assignment = static fn (string $x): int => 2,
        (static fn (): int => 2)(...),
    );
    $stages('hello');
}
