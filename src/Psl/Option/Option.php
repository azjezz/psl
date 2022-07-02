<?php

declare(strict_types=1);

namespace Psl\Option;

use Closure;

/**
 * @template T
 */
final class Option
{
    /**
     * @param ?array{T} $option
     *
     * @internal
     */
    private function __construct(
        private readonly null|array $option,
    ) {
    }

    /**
     * Create an option with some value.
     *
     * @template Tv
     *
     * @param Tv $value
     *
     * @return Option<Tv>
     */
    public static function some(mixed $value): Option
    {
        return new self([$value]);
    }

    /**
     * Create an option with none value.
     *
     * @template Tn
     *
     * @return Option<Tn>
     */
    public static function none(): Option
    {
        return new self(null);
    }

    /**
     * Returns true if the option is a some value.
     */
    public function isSome(): bool
    {
        return $this->option !== null;
    }

    /**
     * Returns true if the option is a some and the value inside of it matches a predicate.
     *
     * @param (Closure(T): bool) $predicate
     */
    public function isSomeAnd(Closure $predicate): bool
    {
        return $this->option !== null && $predicate($this->option[0]);
    }

    /**
     * Returns true if the option is a none.
     */
    public function isNone(): bool
    {
        return $this->option === null;
    }

    /**
     * Returns the contained Some value, consuming the self value.
     *
     * because this function may throw, its use is generally discouraged.
     * Instead, prefer to use `Option::unwrapOr()`, `Option::unwrapOrElse()`.
     *
     * @throws Exception\NoneException If the option is none.
     *
     * @return T
     */
    public function unwrap(): mixed
    {
        if ($this->option !== null) {
            return $this->option[0];
        }

        throw new Exception\NoneException('Attempting to unwrap a none option.');
    }

    /**
     * Returns the contained some value or the provided default.
     *
     * @note:   Arguments passed to `Option::unwrapOr()` are eagerly evaluated;
     *          if you are passing the result of a function call, it is recommended to use `Option::unwrapOrElse()`, which is lazily evaluated.
     *
     * @param T $default
     *
     * @return T
     */
    public function unwrapOr(mixed $default): mixed
    {
        if ($this->option !== null) {
            return $this->option[0];
        }

        return $default;
    }

    /**
     * Returns the contained some value or computes it from a closure.
     *
     * @param (Closure(): T) $default
     *
     * @return T
     */
    public function unwrapOrElse(Closure $default): mixed
    {
        if ($this->option !== null) {
            return $this->option[0];
        }

        return $default();
    }

    /**
     * Return none if either `$this`, or `$other` options are none, otherwise returns `$other`.
     *
     * @template Tu
     *
     * @param Option<Tu> $other
     *
     * @return Option<Tu>
     */
    public function and(Option $other): Option
    {
        if ($this->option !== null && $other->option !== null) {
            return $other;
        }

        return none();
    }

    /**
     * Returns the option if it contains a value, otherwise returns $option.
     *
     * @note:   Arguments passed to `Option::or()` are eagerly evaluated;
     *          if you are passing the result of a function call, it is recommended to use `Option::orElse()`, which is lazily evaluated.
     *
     * @param Option<T> $option
     *
     * @return Option<T>
     */
    public function or(Option $option): Option
    {
        if ($this->option !== null) {
            return $this;
        }

        return $option;
    }

    /**
     * Returns none if the option is none, otherwise calls `$predicate` with the wrapped value and returns:
     *  - Option<T>::some() if `$predicate` returns true (where t is the wrapped value), and
     *  - Option<T>::none() if `$predicate` returns false.
     *
     * @param (Closure(T): bool) $predicate
     *
     * @return Option<T>
     */
    public function filter(Closure $predicate): Option
    {
        if ($this->option !== null) {
            return $predicate($this->option[0]) ? $this : none();
        }

        return $this;
    }

    /**
     * Returns true if the option is a `Option<T>::some()` value containing the given value.
     *
     * @psalm-assert-if-true T $value
     */
    public function contains(mixed $value): bool
    {
        if ($this->option !== null) {
            return $this->option[0] === $value;
        }

        return false;
    }

    /**
     * Maps an `Option<T>` to `Option<Tu>` by applying a function to a contained value.
     *
     * @template Tu
     *
     * @param (Closure(T): Tu) $closure
     *
     * @return Option<Tu>
     */
    public function map(Closure $closure): Option
    {
        if ($this->option !== null) {
            return some($closure($this->option[0]));
        }

        /** @var Option<Tu> */
        return $this;
    }

    /**
     * Applies a function to the contained value (if some),
     * or returns `Option<Tu>::some()` with the provided `$default` value (if none).
     *
     * @note:   arguments passed to `Option::mapOr()` are eagerly evaluated;
     *          if you are passing the result of a function call, it is recommended to use `Option::mapOrElse()`, which is lazily evaluated.
     *
     * @template Tu
     *
     * @param (Closure(T): Tu) $closure
     * @param Tu $default
     *
     * @return Option<Tu>
     */
    public function mapOr(Closure $closure, mixed $default): Option
    {
        if ($this->option !== null) {
            return some($closure($this->option[0]));
        }

        return some($default);
    }

    /**
     * Applies a function to the contained value (if some),
     * or computes a default function result (if none).
     *
     * @template Tu
     *
     * @param (Closure(T): Tu)  $closure
     * @param (Closure(): Tu)   $else
     *
     * @return Option<Tu>
     */
    public function mapOrElse(Closure $closure, Closure $default): Option
    {
        if ($this->option !== null) {
            return some($closure($this->option[0]));
        }

        return some($default());
    }
}
