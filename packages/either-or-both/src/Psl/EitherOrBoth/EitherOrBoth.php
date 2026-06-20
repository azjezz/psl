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
 * @api
 */
interface EitherOrBoth<out TLeft, out TRight> extends Comparison\Equable<EitherOrBoth<mixed, mixed>>
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
     * @psalm-mutation-free
     */
    public function getLeft(): TLeft;

    /**
     * Returns the contained right value.
     *
     * @throws Exception\MissingRightException If this is a Left.
     *
     * @psalm-mutation-free
     */
    public function getRight(): TRight;

    /**
     * Converts the left value to an Option. Some when a left value is present, None otherwise.
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option<TLeft>;

    /**
     * Converts the right value to an Option. Some when a right value is present, None otherwise.
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option<TRight>;

    /**
     * Apply the same closure to whichever side(s) are present.
     *
     * On {@see Both} the closure runs twice, once per side, independently.
     *
     * @param (Closure(TLeft|TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function map<TResult>(Closure $closure): EitherOrBoth<TResult, TResult>;

    /**
     * Map the left side if present, leave the right side untouched.
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapLeft<TResult>(Closure $closure): EitherOrBoth<TResult, TRight>;

    /**
     * Map the right side if present, leave the left side untouched.
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapRight<TResult>(Closure $closure): EitherOrBoth<TLeft, TResult>;

    /**
     * Map each side independently with its own closure.
     *
     * On {@see Left}, only the left closure runs; on {@see Right}, only the right;
     * on {@see Both}, both run.
     *
     * @param (Closure(TLeft): TResultLeft)   $left
     * @param (Closure(TRight): TResultRight) $right
     *
     * @param-immediately-invoked-callable $left
     * @param-immediately-invoked-callable $right
     */
    public function mapAny<TResultLeft, TResultRight>(Closure $left, Closure $right): EitherOrBoth<TResultLeft, TResultRight>;

    /**
     * Swap the Left and Right sides.
     *
     * {@see Left} becomes {@see Right}, {@see Right} becomes {@see Left},
     * and {@see Both}(l, r) becomes {@see Both}(r, l).
     *
     * @psalm-mutation-free
     */
    public function swap(): EitherOrBoth<TRight, TLeft>;

    /**
     * Pattern-match on the variant and dispatch to the matching closure.
     *
     * Argument order is positional: left first, right second, both third.
     * No happy-path convention applies — the three variants are equal citizens.
     *
     * @param (Closure(TLeft): TResult)         $left  Called when this is a Left.
     * @param (Closure(TRight): TResult)        $right Called when this is a Right.
     * @param (Closure(TLeft, TRight): TResult) $both  Called when this is a Both.
     *
     * @param-immediately-invoked-callable $left
     * @param-immediately-invoked-callable $right
     * @param-immediately-invoked-callable $both
     */
    public function proceed<TResult>(Closure $left, Closure $right, Closure $both): TResult;

    /**
     * Run a side-effect closure on the contained value(s) and return self unchanged.
     *
     * The closure is called once on {@see Left} / {@see Right} with the present value,
     * and twice on {@see Both} -- once per side, mirroring {@see map()}.
     *
     * @param (Closure(TLeft|TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function apply(Closure $closure): EitherOrBoth<TLeft, TRight>;

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
