<?php

declare(strict_types=1);

namespace Psl\Iter;

use Countable;
use Psl;
use SeekableIterator;

/**
 * A SeekableIterator implementation that support any type of keys.
 *
 * You can use this class to turn any iterable into a seekable, rewindable, countable iterator.
 *
 * Note: if you want to turn a generator into a rewindable iterator, its recommended to use Gen\rewindable instead,
 *          Otherwise the given generator will be exhausted immediately.
 *
 * @template-covariant Tk
 * @template-covariant Tv
 *
 * @template-implements SeekableIterator<Tk, Tv>
 */
final class Iterator implements SeekableIterator, Countable
{
    /**
     * @psalm-var array{0: array<int, Tk>, 1: array<int, Tv>}
     */
    private array $data = [[], []];

    private int $position = 0;

    private int $count;

    /**
     * @psalm-param iterable<Tk, Tv> $iterable
     */
    public function __construct(iterable $iterable)
    {
        foreach ($iterable as $k => $v) {
            $this->data[0][] = $k;
            $this->data[1][] = $v;
        }

        $this->count = count($this->data[0]);
    }

    /**
     * Return the current element.
     *
     * @psalm-return Tv
     *
     * @throws Psl\Exception\InvariantViolationException If the iterator is invalid.
     */
    public function current()
    {
        Psl\invariant($this->valid(), 'Invalid Iterator');

        return $this->data[1][$this->position];
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element.
     *
     * @psalm-return Tk
     *
     * @throws Psl\Exception\InvariantViolationException If the iterator is invalid.
     */
    public function key()
    {
        Psl\invariant($this->valid(), 'Invalid iterator');

        return $this->data[0][$this->position];
    }

    /**
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        return $this->position >= 0 && $this->position < $this->count();
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Seeks to a position.
     *
     * @param int $position
     *
     * @throws Psl\Exception\InvariantViolationException If $position is out-of-bounds.
     */
    public function seek($position): void
    {
        Psl\invariant($position >= 0 && $position < $this->count(), 'Position is out-of-bounds.');

        $this->position = (int) $position;
    }

    public function count(): int
    {
        return $this->count;
    }
}
