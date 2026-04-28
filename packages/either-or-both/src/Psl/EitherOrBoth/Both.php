<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * The Both variant of {@see EitherOrBoth}: a left value and a right value are both present.
 *
 * @template-covariant TLeft
 * @template-covariant TRight
 *
 * @implements EitherOrBoth<TLeft, TRight>
 *
 * @api
 */
final readonly class Both implements EitherOrBoth
{
    /**
     * @var TLeft
     */
    private mixed $left;

    /**
     * @var TRight
     */
    private mixed $right;

    /**
     * @param TLeft  $left
     * @param TRight $right
     *
     * @psalm-mutation-free
     */
    public function __construct(mixed $left, mixed $right)
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
     * @return TLeft
     *
     * @psalm-mutation-free
     */
    public function getLeft(): mixed
    {
        return $this->left;
    }

    /**
     * @return TRight
     *
     * @psalm-mutation-free
     */
    public function getRight(): mixed
    {
        return $this->right;
    }

    /**
     * @return Option\Option<TLeft>
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option
    {
        return Option\some($this->left);
    }

    /**
     * @return Option\Option<TRight>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option
    {
        return Option\some($this->right);
    }

    /**
     * Applies the closure to both sides independently.
     *
     * @template TResult
     *
     * @param (Closure(TLeft|TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Both<TResult, TResult>
     */
    public function map(Closure $closure): Both
    {
        return new Both($closure($this->left), $closure($this->right));
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Both<TResult, TRight>
     */
    public function mapLeft(Closure $closure): Both
    {
        return new Both($closure($this->left), $this->right);
    }

    /**
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Both<TLeft, TResult>
     */
    public function mapRight(Closure $closure): Both
    {
        return new Both($this->left, $closure($this->right));
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TLeft): TResultLeft)   $left
     * @param (Closure(TRight): TResultRight) $right
     *
     * @param-immediately-invoked-callable $left
     * @param-immediately-invoked-callable $right
     *
     * @return Both<TResultLeft, TResultRight>
     */
    public function mapAny(Closure $left, Closure $right): Both
    {
        return new Both($left($this->left), $right($this->right));
    }

    /**
     * @return Both<TRight, TLeft>
     *
     * @psalm-mutation-free
     */
    public function swap(): Both
    {
        return new Both($this->right, $this->left);
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult)         $left
     * @param (Closure(TRight): TResult)        $right
     * @param (Closure(TLeft, TRight): TResult) $both
     *
     * @param-immediately-invoked-callable $both
     *
     * @return TResult
     */
    public function proceed(Closure $left, Closure $right, Closure $both): mixed
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
     *
     * @return Both<TLeft, TRight>
     */
    public function apply(Closure $closure): Both
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

    /**
     * @param EitherOrBoth<TLeft, TRight> $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }
}
