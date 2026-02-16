<?php

declare(strict_types=1);

namespace Psl\Either;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * Represents a value of one of two possible types (a disjoint union).
 *
 * An instance of Either is either a {@see Left} or a {@see Right}.
 *
 * By convention, Left represents the failure/error case and Right represents the success case.
 *
 * @template TLeft
 * @template TRight
 *
 * @extends Comparison\Comparable<Either<TLeft, TRight>>
 * @extends Comparison\Equable<Either<TLeft, TRight>>
 */
interface Either extends Comparison\Comparable, Comparison\Equable
{
    /**
     * Returns true if this is a Right value.
     *
     * @return bool
     *
     * @psalm-mutation-free
     */
    public function isRight(): bool;

    /**
     * Returns true if this is a Left value.
     *
     * @return bool
     *
     * @psalm-mutation-free
     */
    public function isLeft(): bool;

    /**
     * Returns the contained Right value.
     *
     * @throws Exception\LeftException If this is a Left.
     *
     * @return TRight
     *
     * @psalm-mutation-free
     */
    public function getRight(): mixed;

    /**
     * Returns the contained Left value.
     *
     * @throws Exception\RightException If this is a Right.
     *
     * @return TLeft
     *
     * @psalm-mutation-free
     */
    public function getLeft(): mixed;

    /**
     * Returns the contained Right value, or the provided default.
     *
     * @note Arguments passed are eagerly evaluated; use {@see getRightOrElse()} for lazy evaluation.
     *
     * @template T
     *
     * @param T $default
     *
     * @return TRight|T
     *
     * @psalm-mutation-free
     */
    public function getRightOr(mixed $default): mixed;

    /**
     * Returns the contained Left value, or the provided default.
     *
     * @note Arguments passed are eagerly evaluated; use {@see getLeftOrElse()} for lazy evaluation.
     *
     * @template T
     *
     * @param T $default
     *
     * @return TLeft|T
     *
     * @psalm-mutation-free
     */
    public function getLeftOr(mixed $default): mixed;

    /**
     * Returns the contained Right value, or computes it from the Left value using the given closure.
     *
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return TRight|TResult
     */
    public function getRightOrElse(Closure $closure): mixed;

    /**
     * Returns the contained Left value, or computes it from the Right value using the given closure.
     *
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return TLeft|TResult
     */
    public function getLeftOrElse(Closure $closure): mixed;

    /**
     * Converts the Right value to an Option, returning None if this is a Left.
     *
     * @return Option\Option<TRight>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option;

    /**
     * Converts the Left value to an Option, returning None if this is a Right.
     *
     * @return Option\Option<TLeft>
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option;

    /**
     * Maps an Either by applying a function to the contained value, whether Left or Right.
     *
     * @template TResult
     *
     * @param (Closure(TLeft|TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResult, TResult>
     */
    public function map(Closure $closure): Either;

    /**
     * Maps an Either by applying a function to the contained Right value,
     * leaving a Left value untouched.
     *
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TLeft, TResult>
     */
    public function mapRight(Closure $closure): Either;

    /**
     * Maps an Either by applying a function to the contained Left value,
     * leaving a Right value untouched.
     *
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResult, TRight>
     */
    public function mapLeft(Closure $closure): Either;

    /**
     * Applies a function to the contained value and returns the resulting Either.
     *
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TLeft|TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, TResultRight>
     */
    public function flatMap(Closure $closure): Either;

    /**
     * Applies a function to the contained Right value and returns the resulting Either,
     * leaving a Left value untouched.
     *
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TLeft|TResultLeft, TResultRight>
     */
    public function flatMapRight(Closure $closure): Either;

    /**
     * Applies a function to the contained Left value and returns the resulting Either,
     * leaving a Right value untouched.
     *
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, TRight|TResultRight>
     */
    public function flatMapLeft(Closure $closure): Either;

    /**
     * Matches the contained value with the provided closures and returns the result.
     *
     * The right closure is the first parameter (happy path first),
     * consistent with {@see \Psl\Result\ResultInterface::proceed()} and {@see Option\Option::proceed()}.
     *
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $right A closure called when the Either is Right.
     *
     * @param-immediately-invoked-callable $right
     *
     * @param (Closure(TLeft): TResult) $left A closure called when the Either is Left.
     *
     * @param-immediately-invoked-callable $left
     *
     * @return TResult
     */
    public function proceed(Closure $right, Closure $left): mixed;

    /**
     * Applies a function to the contained value and returns the original Either.
     *
     * @param (Closure(TLeft|TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TLeft, TRight>
     */
    public function apply(Closure $closure): Either;

    /**
     * Swaps the Left and Right sides of this Either.
     *
     * @return Either<TRight, TLeft>
     *
     * @psalm-mutation-free
     */
    public function swap(): Either;

    /**
     * Returns true if this is a Right containing the given value.
     *
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool;

    /**
     * Returns true if this is a Left containing the given value.
     *
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool;
}
