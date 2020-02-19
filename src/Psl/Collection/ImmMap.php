<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Iter;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @implements ConstMap<Tk, Tv>
 */
final class ImmMap implements ConstMap
{
    /**
     * @psalm-var array<int, array{0: Tk, 1: Tv }>
     */
    private array $elements = [];

    /**
     * @psalm-param iterable<Tk, Tv> $values
     */
    public function __construct(iterable $values)
    {
        foreach ($values as $k => $v) {
            $this->elements[] = [$k, $v];
        }
    }

    /**
     * Get access to the items in the collection.
     *
     * @psalm-return Vector<Pair<Tk, Tv>>
     */
    public function items(): Vector
    {
        $values = [];
        foreach ($this->elements as [$k, $v]) {
            $values[] = new Pair($k, $v);
        }

        return new Vector($values);
    }

    /**
     * Is the collection empty?
     */
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the collection.
     */
    public function count(): int
    {
        return Iter\count($this->elements);
    }

    /**
     * Get an array copy of the the collection.
     *
     * @psalm-return array<Tk, Tv>
     * @return array
     */
    public function toArray(): array
    {
        $elements = [];
        foreach ($this->elements as [$key, $value]) {
            $elements[$key] = $value;
        }

        return $elements;
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param Tk $k
     *
     * @psalm-return Tv
     */
    public function at($k)
    {
        if (!$this->containsKey($k)) {
            Psl\invariant_violation('Key (%s) is out-of-bound.', $k);
        }

        /** @psalm-var Tv */
        return $this->get($k);
    }

    /**
     * Determines if the specified key is in the current collection.
     *
     * @psalm-param Tk $k
     */
    public function containsKey($k): bool
    {
        foreach ($this->elements as [$key, $value]) {
            if ($k === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param  Tk $k
     *
     * @psalm-return Tv|null
     */
    public function get($k)
    {
        foreach ($this->elements as [$key, $value]) {
            if ($k === $key) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Determines if the specified key is in the current `ImmMap`.
     *
     * This function is interchangeable with `containsKey()`.
     *
     * @psalm-param Tk $m
     *
     * @psalm-return bool - `true` if the value is in the current `ImmMap`; `false` otherwise
     */
    public function contains($m): bool
    {
        return $this->containsKey($m);
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iter\Iterator<Tk, Tv> - An instance of an object implementing Iterator
     */
    public function getIterator(): Iter\Iterator
    {
        /** @var \Generator<Tk, Tv, mixed, void> $gen */
        $gen = (function (): \Generator {
            foreach ($this->elements as [$k, $v]) {
                yield $k => $v;
            }
        })();

        /** @psalm-var Iter\Iterator<Tk, Tv> */
        return new Iter\Iterator($gen);
    }
}
