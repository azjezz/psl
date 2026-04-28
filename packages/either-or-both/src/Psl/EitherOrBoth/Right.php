<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * The Right variant of {@see EitherOrBoth}: only a right value is present.
 *
 * @template-covariant TRight
 *
 * @implements EitherOrBoth<never, TRight>
 *
 * @api
 */
final readonly class Right implements EitherOrBoth
{
    /**
     * @var TRight
     */
    private mixed $value;

    /**
     * @param TRight $value
     *
     * @psalm-mutation-free
     */
    public function __construct(mixed $value)
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
     * @return TRight
     *
     * @psalm-mutation-free
     */
    public function getRight(): mixed
    {
        return $this->value;
    }

    /**
     * @return Option\Option<never>
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option
    {
        return Option\none();
    }

    /**
     * @return Option\Option<TRight>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option
    {
        return Option\some($this->value);
    }

    /**
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Right<TResult>
     */
    public function map(Closure $closure): Right
    {
        return new Right($closure($this->value));
    }

    /**
     * @template TResult
     *
     * @param (Closure(never): TResult) $closure
     *
     * @return Right<TRight>
     *
     * @psalm-mutation-free
     */
    public function mapLeft(Closure $closure): Right
    {
        return $this;
    }

    /**
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Right<TResult>
     */
    public function mapRight(Closure $closure): Right
    {
        return new Right($closure($this->value));
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(never): TResultLeft)    $left
     * @param (Closure(TRight): TResultRight)  $right
     *
     * @param-immediately-invoked-callable $right
     *
     * @return Right<TResultRight>
     */
    public function mapAny(Closure $left, Closure $right): Right
    {
        return new Right($right($this->value));
    }

    /**
     * @return Left<TRight>
     *
     * @psalm-mutation-free
     */
    public function swap(): Left
    {
        return new Left($this->value);
    }

    /**
     * @template TResult
     *
     * @param (Closure(never): TResult)          $left
     * @param (Closure(TRight): TResult)         $right
     * @param (Closure(never, TRight): TResult)  $both
     *
     * @param-immediately-invoked-callable $right
     *
     * @return TResult
     */
    public function proceed(Closure $left, Closure $right, Closure $both): mixed
    {
        return $right($this->value);
    }

    /**
     * @param (Closure(TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Right<TRight>
     */
    public function apply(Closure $closure): Right
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

    /**
     * @param EitherOrBoth<mixed, TRight> $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }
}
