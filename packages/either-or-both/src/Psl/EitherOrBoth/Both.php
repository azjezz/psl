<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * The Both variant of {@see EitherOrBoth}: a left value and a right value are both present.
 *
 * @api
 */
final readonly class Both<out TLeft, out TRight> implements EitherOrBoth<TLeft, TRight>
{
    private TLeft $left;

    private TRight $right;

    /**
     * @psalm-mutation-free
     */
    public function __construct(TLeft $left, TRight $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @return false
     *
     * @psalm-mutation-free
     */
    public function isLeft(): bool
    {
        return false;
    }

    /**
     * @return false
     *
     * @psalm-mutation-free
     */
    public function isRight(): bool
    {
        return false;
    }

    /**
     * @return true
     *
     * @psalm-mutation-free
     */
    public function isBoth(): bool
    {
        return true;
    }

    /**
     * @return true
     *
     * @psalm-mutation-free
     */
    public function hasLeft(): bool
    {
        return true;
    }

    /**
     * @return true
     *
     * @psalm-mutation-free
     */
    public function hasRight(): bool
    {
        return true;
    }

    /**
     * @psalm-mutation-free
     */
    public function getLeft(): TLeft
    {
        return $this->left;
    }

    /**
     * @psalm-mutation-free
     */
    public function getRight(): TRight
    {
        return $this->right;
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option<TLeft>
    {
        return Option\some::<TLeft>($this->left);
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option<TRight>
    {
        return Option\some::<TRight>($this->right);
    }

    /**
     * Applies the closure to both sides independently.
     *
     * @param (Closure(TLeft|TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function map<TResult>(Closure $closure): Both<TResult, TResult>
    {
        return new Both::<TResult, TResult>($closure($this->left), $closure($this->right));
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapLeft<TResult>(Closure $closure): Both<TResult, TRight>
    {
        return new Both::<TResult, TRight>($closure($this->left), $this->right);
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapRight<TResult>(Closure $closure): Both<TLeft, TResult>
    {
        return new Both::<TLeft, TResult>($this->left, $closure($this->right));
    }

    /**
     * @param (Closure(TLeft): TResultLeft)   $left
     * @param (Closure(TRight): TResultRight) $right
     *
     * @param-immediately-invoked-callable $left
     * @param-immediately-invoked-callable $right
     */
    public function mapAny<TResultLeft, TResultRight>(Closure $left, Closure $right): Both<TResultLeft, TResultRight>
    {
        return new Both::<TResultLeft, TResultRight>($left($this->left), $right($this->right));
    }

    /**
     * @psalm-mutation-free
     */
    public function swap(): Both<TRight, TLeft>
    {
        return new Both::<TRight, TLeft>($this->right, $this->left);
    }

    /**
     * @param (Closure(TLeft): TResult)         $left
     * @param (Closure(TRight): TResult)        $right
     * @param (Closure(TLeft, TRight): TResult) $both
     *
     * @param-immediately-invoked-callable $both
     */
    public function proceed<TResult>(Closure $left, Closure $right, Closure $both): TResult
    {
        return $both($this->left, $this->right);
    }

    /**
     * Applies the closure to both sides independently. Runs twice: once with the
     * left value, once with the right. Same invocation shape as {@see map()}.
     *
     * @param (Closure(TLeft|TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function apply(Closure $closure): Both<TLeft, TRight>
    {
        $closure($this->left);
        $closure($this->right);

        return $this;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool
    {
        return $this->left === $value;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool
    {
        return $this->right === $value;
    }

    public function equals(EitherOrBoth<mixed, mixed> $other): bool
    {
        return Comparison\equal::<EitherOrBoth<mixed, mixed>>($this, $other);
    }
}
