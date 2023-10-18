<?php

declare(strict_types=1);

namespace Psl\Option;

use Closure;
use Psl\Comparison;
use Psl\Type;

/**
 * @template T
 *
 * @implements Comparison\Comparable<Option<T>>
 * @implements Comparison\Equable<Option<T>>
 */
final class Option implements Comparison\Comparable, Comparison\Equable
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
     * @return Option<never>
     */
    public static function none(): Option
    {
        /** @var Option<never> */
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
     * @template O
     *
     * @param O $default
     *
     * @return T|O
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
     * @template O
     *
     * @param (Closure(): O) $default
     *
     * @return T|O
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
     * Matches the contained option value with the provided closures and returns the result.
     *
     * @template Ts
     *
     * @param (Closure(T): Ts) $some A closure to be called when the option is some.
     *                               The closure must accept the option value as its only argument and can return a value.
     *                               Example: `fn($value) => $value + 10`
     * @param (Closure(): Ts) $none A closure to be called when the option is none.
     *                              The closure must not accept any arguments and can return a value.
     *                              Example: `fn() => 'Default value'`
     *
     * @return Ts The result of calling the appropriate closure.
     */
    public function proceed(Closure $some, Closure $none): mixed
    {
        if ($this->option !== null) {
            return $some($this->option[0]);
        }

        return $none();
    }

    /**
     * Applies a function to a contained value and returns the original `Option<T>`.
     *
     * @param (Closure(T): void) $closure
     *
     * @return Option<T>
     */
    public function apply(Closure $closure): Option
    {
        if ($this->option !== null) {
            $closure($this->option[0]);
        }

        return $this;
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
     * Maps an `Option<T>` to `Option<Tu>` by applying a function to a contained value that returns an Option<Tu>.
     *
     * @template Tu
     *
     * @param (Closure(T): Option<Tu>) $closure
     *
     * @return Option<Tu>
     */
    public function andThen(Closure $closure): Option
    {
        if ($this->option !== null) {
            return $closure($this->option[0]);
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
     * @param (Closure(T): Tu) $closure
     * @param (Closure(): Tu) $else
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

    /**
     * @param Option<T> $other
     */
    public function compare(mixed $other): Comparison\Order
    {
        $aIsNone = $this->isNone();
        $bIsNone = $other->isNone();

        return match (true) {
            $aIsNone || $bIsNone => Comparison\compare($bIsNone, $aIsNone),
            default => Comparison\compare($this->unwrap(), $other->unwrap())
        };
    }

    /**
     * @param Option<T> $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }

    /**
     * Combines two `Option` values into a single `Option` containing a tuple of the two inner values.
     * If either of the `Option`s is `None`, the resulting `Option` will also be `None`.
     *
     * @template Tu
     *
     * @param Option<Tu> $other The other `Option` to zip with.
     *
     * @return Option<array{T, Tu}> The resulting `Option` containing the combined tuple or `None`.
     */
    public function zip(Option $other): Option
    {
        return $this->andThen(static function ($a) use ($other) {
            return $other->map(static fn($b) => [$a, $b]);
        });
    }

    /**
     * Applies the provided closure to the value contained in this `Option` and the value contained in the $other `Option`,
     * and returns a new `Option` containing the result of the closure.
     *
     * @template Tu
     * @template Tr
     *
     * @param Option<Tu> $other The Option to zip with.
     * @param (Closure(T, Tu): Tr) $closure The closure to apply to the values.
     *
     * @return Option<Tr> The new `Option` containing the result of applying the closure to the values,
     *                    or `None` if either this or the $other `Option is `None`.
     */
    public function zipWith(Option $other, Closure $closure): Option
    {
        return $this->andThen(
            /** @param T $a */
            static function ($a) use ($other, $closure) {
                return $other->map(
                    /** @param Tu $b */
                    static fn ($b) => $closure($a, $b)
                );
            }
        );
    }

    /**
     * @template Tv
     * @template Tr
     *
     * @psalm-if-this-is Option<array{Tv, Tr}>
     *
     * @throws Type\Exception\AssertException
     *
     * @return array{Option<Tv>, Option<Tr>}
     */
    public function unzip(): array
    {
        if ($this->option === null) {
            return [none(), none()];
        }

        // Assertion done in a separate variable to avoid Psalm inferring the type of $this->option as mixed
        $option = $this->option[0];
        Type\shape([Type\mixed(), Type\mixed()])->assert($option);

        [$a, $b] = $option;

        return [some($a), some($b)];
    }
}
