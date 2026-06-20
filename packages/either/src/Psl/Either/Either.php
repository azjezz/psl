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
 * @api
 */
interface Either<out TLeft, out TRight> extends Comparison\Comparable<Either<mixed, mixed>>, Comparison\Equable<Either<mixed, mixed>>
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
     * @psalm-mutation-free
     */
    public function getRight(): TRight;

    /**
     * Returns the contained Left value.
     *
     * @throws Exception\RightException If this is a Right.
     *
     * @psalm-mutation-free
     */
    public function getLeft(): TLeft;

    /**
     * Returns the contained Right value, or the provided default.
     *
     * @note Arguments passed are eagerly evaluated; use {@see getRightOrElse()} for lazy evaluation.
     *
     * @psalm-mutation-free
     */
    public function getRightOr<T>(T $default): TRight|T;

    /**
     * Returns the contained Left value, or the provided default.
     *
     * @note Arguments passed are eagerly evaluated; use {@see getLeftOrElse()} for lazy evaluation.
     *
     * @psalm-mutation-free
     */
    public function getLeftOr<T>(T $default): TLeft|T;

    /**
     * Returns the contained Right value, or computes it from the Left value using the given closure.
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function getRightOrElse<TResult>(Closure $closure): TRight|TResult;

    /**
     * Returns the contained Left value, or computes it from the Right value using the given closure.
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function getLeftOrElse<TResult>(Closure $closure): TLeft|TResult;

    /**
     * Converts the Right value to an Option, returning None if this is a Left.
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option<TRight>;

    /**
     * Converts the Left value to an Option, returning None if this is a Right.
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option<TLeft>;

    /**
     * Maps an Either by applying a function to the contained value, whether Left or Right.
     *
     * @param (Closure(TLeft|TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function map<TResult>(Closure $closure): Either<TResult, TResult>;

    /**
     * Maps an Either by applying a function to the contained Right value,
     * leaving a Left value untouched.
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapRight<TResult>(Closure $closure): Either<TLeft, TResult>;

    /**
     * Maps an Either by applying a function to the contained Left value,
     * leaving a Right value untouched.
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapLeft<TResult>(Closure $closure): Either<TResult, TRight>;

    /**
     * Applies a function to the contained value and returns the resulting Either.
     *
     * @param (Closure(TLeft|TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function flatMap<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, TResultRight>;

    /**
     * Applies a function to the contained Right value and returns the resulting Either,
     * leaving a Left value untouched.
     *
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function flatMapRight<TResultLeft, TResultRight>(Closure $closure): Either<TLeft|TResultLeft, TResultRight>;

    /**
     * Applies a function to the contained Left value and returns the resulting Either,
     * leaving a Right value untouched.
     *
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function flatMapLeft<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, TRight|TResultRight>;

    /**
     * Matches the contained value with the provided closures and returns the result.
     *
     * The right closure is the first parameter (happy path first),
     * consistent with {@see \Psl\Result\ResultInterface::proceed()} and {@see Option\Option::proceed()}.
     *
     * @param (Closure(TRight): TResult) $right A closure called when the Either is Right.
     *
     * @param-immediately-invoked-callable $right
     *
     * @param (Closure(TLeft): TResult) $left A closure called when the Either is Left.
     *
     * @param-immediately-invoked-callable $left
     */
    public function proceed<TResult>(Closure $right, Closure $left): TResult;

    /**
     * Applies a function to the contained value and returns the original Either.
     *
     * @param (Closure(TLeft|TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function apply(Closure $closure): Either<TLeft, TRight>;

    /**
     * Swaps the Left and Right sides of this Either.
     *
     * @psalm-mutation-free
     */
    public function swap(): Either<TRight, TLeft>;

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
