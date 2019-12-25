<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;

/**
 * A SeekableIterator implementation that support any type of keys.
 *
 * You can use this class to turn any iterable into a seekable, rewindable, countable iterator.
 *
 * @template-covariant TKey
 * @template-covariant TValue
 *
 * @template-implements \SeekableIterator<TKey, TValue>
 */
final class Iterator implements \SeekableIterator, \Countable
{
    /**
     * @psalm-var array{0: array<int, TKey>, 1: array<int, TValue>}
     */
    private array $data = [[], []];

    private int $position = 0;

    private int $count;

    /**
     * @psalm-param iterable<TKey, TValue> $iterable
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
     * @psalm-return TValue
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
     * @psalm-return TKey
     */
    public function key()
    {
        Psl\invariant($this->valid(), 'Invalid Iterator');

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
     */
    public function seek($position): void
    {
        Psl\invariant($position >= 0 && $position < $this->count(), 'Position (%d) is out-of-bound.', $position);

        $this->position = (int) $position;
    }

    public function count(): int
    {
        return $this->count;
    }
}
