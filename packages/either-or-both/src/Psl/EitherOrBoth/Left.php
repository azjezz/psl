<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * The Left variant of {@see EitherOrBoth}: only a left value is present.
 *
 * @template-covariant TLeft
 *
 * @implements EitherOrBoth<TLeft, never>
 *
 * @api
 */
final readonly class Left implements EitherOrBoth
{
    /**
     * @var TLeft
     */
    private mixed $value;

    /**
     * @param TLeft $value
     *
     * @psalm-mutation-free
     */
    public function __construct(mixed $value)
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
     * @return TLeft
     *
     * @psalm-mutation-free
     */
    public function getLeft(): mixed
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
     * @return Option\Option<TLeft>
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option
    {
        return Option\some($this->value);
    }

    /**
     * @return Option\Option<never>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option
    {
        return Option\none();
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TResult>
     */
    public function map(Closure $closure): Left
    {
        return new Left($closure($this->value));
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TResult>
     */
    public function mapLeft(Closure $closure): Left
    {
        return new Left($closure($this->value));
    }

    /**
     * @template TResult
     *
     * @param (Closure(never): TResult) $closure
     *
     * @return Left<TLeft>
     *
     * @psalm-mutation-free
     */
    public function mapRight(Closure $closure): Left
    {
        return $this;
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TLeft): TResultLeft)   $left
     * @param (Closure(never): TResultRight) $right
     *
     * @param-immediately-invoked-callable $left
     *
     * @return Left<TResultLeft>
     */
    public function mapAny(Closure $left, Closure $right): Left
    {
        return new Left($left($this->value));
    }

    /**
     * @return Right<TLeft>
     *
     * @psalm-mutation-free
     */
    public function swap(): Right
    {
        return new Right($this->value);
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult)          $left
     * @param (Closure(never): TResult)          $right
     * @param (Closure(TLeft, never): TResult)   $both
     *
     * @param-immediately-invoked-callable $left
     *
     * @return TResult
     */
    public function proceed(Closure $left, Closure $right, Closure $both): mixed
    {
        return $left($this->value);
    }

    /**
     * @param (Closure(TLeft): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TLeft>
     */
    public function apply(Closure $closure): Left
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

    /**
     * @param EitherOrBoth<TLeft, mixed> $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }
}
