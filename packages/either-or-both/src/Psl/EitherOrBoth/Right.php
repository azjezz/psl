<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * The Right variant of {@see EitherOrBoth}: only a right value is present.
 *
 * @api
 */
final readonly class Right<out TRight> implements EitherOrBoth<never, TRight>
{
    private TRight $value;

    /**
     * @psalm-mutation-free
     */
    public function __construct(TRight $value)
    {
        $this->value = $value;
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
     * @return true
     *
     * @psalm-mutation-free
     */
    public function isRight(): bool
    {
        return true;
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
     * @return false
     *
     * @psalm-mutation-free
     */
    public function hasLeft(): bool
    {
        return false;
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
     * @throws Exception\MissingLeftException Always, since this is a Right.
     *
     * @psalm-mutation-free
     */
    public function getLeft(): never
    {
        throw new Exception\MissingLeftException('Attempting to get a left value from a right.');
    }

    /**
     * @psalm-mutation-free
     */
    public function getRight(): TRight
    {
        return $this->value;
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option<never>
    {
        return Option\none();
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option<TRight>
    {
        return Option\some::<TRight>($this->value);
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function map<TResult>(Closure $closure): Right<TResult>
    {
        return new Right::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @psalm-mutation-free
     */
    public function mapLeft<TResult>(Closure $closure): Right<TRight>
    {
        return $this;
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapRight<TResult>(Closure $closure): Right<TResult>
    {
        return new Right::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(never): TResultLeft)    $left
     * @param (Closure(TRight): TResultRight)  $right
     *
     * @param-immediately-invoked-callable $right
     */
    public function mapAny<TResultLeft, TResultRight>(Closure $left, Closure $right): Right<TResultRight>
    {
        return new Right::<TResultRight>($right($this->value));
    }

    /**
     * @psalm-mutation-free
     */
    public function swap(): Left<TRight>
    {
        return new Left::<TRight>($this->value);
    }

    /**
     * @param (Closure(never): TResult)          $left
     * @param (Closure(TRight): TResult)         $right
     * @param (Closure(never, TRight): TResult)  $both
     *
     * @param-immediately-invoked-callable $right
     */
    public function proceed<TResult>(Closure $left, Closure $right, Closure $both): TResult
    {
        return $right($this->value);
    }

    /**
     * @param (Closure(TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function apply(Closure $closure): Right<TRight>
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool
    {
        return false;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool
    {
        return $this->value === $value;
    }

    public function equals(EitherOrBoth<mixed, mixed> $other): bool
    {
        return Comparison\equal::<EitherOrBoth<mixed, mixed>>($this, $other);
    }
}
