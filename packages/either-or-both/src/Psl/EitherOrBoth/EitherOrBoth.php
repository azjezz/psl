<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * Represents a value that is either a {@see Left}, a {@see Right}, or {@see Both}.
 *
 * An instance of EitherOrBoth is always exactly one of those three variants.
 *
 * Unlike a two-variant Either, EitherOrBoth carries no failure/success semantics;
 * the three positions are equal citizens. Primary use case: three-way diff of two
 * collections (insert / delete / update events).
 *
 * @template TLeft
 * @template TRight
 *
 * @extends Comparison\Equable<EitherOrBoth<TLeft, TRight>>
 *
 * @api
 */
interface EitherOrBoth extends Comparison\Equable
{
    /**
     * Returns true if this is exclusively a Left (not Both).
     *
     * @psalm-mutation-free
     */
    public function isLeft(): bool;

    /**
     * Returns true if this is exclusively a Right (not Both).
     *
     * @psalm-mutation-free
     */
    public function isRight(): bool;

    /**
     * Returns true if this is a Both.
     *
     * @psalm-mutation-free
     */
    public function isBoth(): bool;

    /**
     * Returns true if a left value is present (Left or Both).
     *
     * @psalm-mutation-free
     */
    public function hasLeft(): bool;

    /**
     * Returns true if a right value is present (Right or Both).
     *
     * @psalm-mutation-free
     */
    public function hasRight(): bool;

    /**
     * Returns the contained left value.
     *
     * @throws Exception\MissingLeftException If this is a Right.
     *
     * @return TLeft
     *
     * @psalm-mutation-free
     */
    public function getLeft(): mixed;

    /**
     * Returns the contained right value.
     *
     * @throws Exception\MissingRightException If this is a Left.
     *
     * @return TRight
     *
     * @psalm-mutation-free
     */
    public function getRight(): mixed;

    /**
     * Converts the left value to an Option. Some when a left value is present, None otherwise.
     *
     * @return Option\Option<TLeft>
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option;

    /**
     * Converts the right value to an Option. Some when a right value is present, None otherwise.
     *
     * @return Option\Option<TRight>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option;

    /**
     * Apply the same closure to whichever side(s) are present.
     *
     * On {@see Both} the closure runs twice, once per side, independently.
     *
     * @template TResult
     *
     * @param (Closure(TLeft|TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return EitherOrBoth<TResult, TResult>
     */
    public function map(Closure $closure): EitherOrBoth;

    /**
     * Map the left side if present, leave the right side untouched.
     *
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return EitherOrBoth<TResult, TRight>
     */
    public function mapLeft(Closure $closure): EitherOrBoth;

    /**
     * Map the right side if present, leave the left side untouched.
     *
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return EitherOrBoth<TLeft, TResult>
     */
    public function mapRight(Closure $closure): EitherOrBoth;

    /**
     * Map each side independently with its own closure.
     *
     * On {@see Left}, only the left closure runs; on {@see Right}, only the right;
     * on {@see Both}, both run.
     *
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TLeft): TResultLeft)   $left
     * @param (Closure(TRight): TResultRight) $right
     *
     * @param-immediately-invoked-callable $left
     * @param-immediately-invoked-callable $right
     *
     * @return EitherOrBoth<TResultLeft, TResultRight>
     */
    public function mapAny(Closure $left, Closure $right): EitherOrBoth;

    /**
     * Swap the Left and Right sides.
     *
     * {@see Left} becomes {@see Right}, {@see Right} becomes {@see Left},
     * and {@see Both}(l, r) becomes {@see Both}(r, l).
     *
     * @return EitherOrBoth<TRight, TLeft>
     *
     * @psalm-mutation-free
     */
    public function swap(): EitherOrBoth;

    /**
     * Pattern-match on the variant and dispatch to the matching closure.
     *
     * Argument order is positional: left first, right second, both third.
     * No happy-path convention applies — the three variants are equal citizens.
     *
     * @template TResult
     *
     * @param (Closure(TLeft): TResult)         $left  Called when this is a Left.
     * @param (Closure(TRight): TResult)        $right Called when this is a Right.
     * @param (Closure(TLeft, TRight): TResult) $both  Called when this is a Both.
     *
     * @param-immediately-invoked-callable $left
     * @param-immediately-invoked-callable $right
     * @param-immediately-invoked-callable $both
     *
     * @return TResult
     */
    public function proceed(Closure $left, Closure $right, Closure $both): mixed;

    /**
     * Run a side-effect closure on the contained value(s) and return self unchanged.
     *
     * The closure is called once on {@see Left} / {@see Right} with the present value,
     * and twice on {@see Both} -- once per side, mirroring {@see map()}.
     *
     * @param (Closure(TLeft|TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return EitherOrBoth<TLeft, TRight>
     */
    public function apply(Closure $closure): EitherOrBoth;

    /**
     * Returns true if a left value is present and equals the given value.
     *
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool;

    /**
     * Returns true if a right value is present and equals the given value.
     *
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool;
}
