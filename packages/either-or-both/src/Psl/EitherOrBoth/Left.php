<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * The Left variant of {@see EitherOrBoth}: only a left value is present.
 *
 * @api
 */
final readonly class Left<out TLeft> implements EitherOrBoth<TLeft, never>
{
    private TLeft $value;

    /**
     * @psalm-mutation-free
     */
    public function __construct(TLeft $value)
    {
        $this->value = $value;
    }

    /**
     * @return true
     *
     * @psalm-mutation-free
     */
    public function isLeft(): bool
    {
        return true;
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
     * @return false
     *
     * @psalm-mutation-free
     */
    public function isBoth(): bool
    {
        return false;
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
     * @return false
     *
     * @psalm-mutation-free
     */
    public function hasRight(): bool
    {
        return false;
    }

    /**
     * @psalm-mutation-free
     */
    public function getLeft(): TLeft
    {
        return $this->value;
    }

    /**
     * @throws Exception\MissingRightException Always, since this is a Left.
     *
     * @psalm-mutation-free
     */
    public function getRight(): never
    {
        throw new Exception\MissingRightException('Attempting to get a right value from a left.');
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option<TLeft>
    {
        return Option\some::<TLeft>($this->value);
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option<never>
    {
        return Option\none();
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function map<TResult>(Closure $closure): Left<TResult>
    {
        return new Left::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapLeft<TResult>(Closure $closure): Left<TResult>
    {
        return new Left::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @psalm-mutation-free
     */
    public function mapRight<TResult>(Closure $closure): Left<TLeft>
    {
        return $this;
    }

    /**
     * @param (Closure(TLeft): TResultLeft)   $left
     * @param (Closure(never): TResultRight) $right
     *
     * @param-immediately-invoked-callable $left
     */
    public function mapAny<TResultLeft, TResultRight>(Closure $left, Closure $right): Left<TResultLeft>
    {
        return new Left::<TResultLeft>($left($this->value));
    }

    /**
     * @psalm-mutation-free
     */
    public function swap(): Right<TLeft>
    {
        return new Right::<TLeft>($this->value);
    }

    /**
     * @param (Closure(TLeft): TResult)          $left
     * @param (Closure(never): TResult)          $right
     * @param (Closure(TLeft, never): TResult)   $both
     *
     * @param-immediately-invoked-callable $left
     */
    public function proceed<TResult>(Closure $left, Closure $right, Closure $both): TResult
    {
        return $left($this->value);
    }

    /**
     * @param (Closure(TLeft): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function apply(Closure $closure): Left<TLeft>
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool
    {
        return false;
    }

    public function equals(EitherOrBoth<mixed, mixed> $other): bool
    {
        return Comparison\equal::<EitherOrBoth<mixed, mixed>>($this, $other);
    }
}
