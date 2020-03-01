<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Iterator;
use Psl;
use Psl\Iter;

/**
 * @template   Tk
 * @template   Tv
 *
 * @implements Iterator<Tk, Tv>
 */
final class RewindableGenerator implements Iterator
{
    /**
     * @psalm-var Iterator<Tk, Tv>
     */
    private Iterator $iterator;

    /**
     * @psalm-var array<int, array{0: Tk, 1: Tv}>
     */
    private array $cache = [];

    /**
     * Whether or not the current value/key pair has been added to the local cache.
     */
    private bool $cached = false;

    /**
     * @psalm-param Generator<Tk, Tv, mixed, void> $generator
     */
    public function __construct(Generator $generator)
    {
        $this->iterator = $generator;
    }

    /**
     * Return the current element.
     *
     * @psalm-return Tv
     */
    public function current()
    {
        Psl\invariant($this->valid(), 'Invalid Iterator');
        $value = $this->iterator->current();
        if ($this->iterator instanceof Generator && !$this->cached) {
            $this->cached = true;
            $this->cache[] = [$this->key(), $value];
        }

        return $value;
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        $this->cached = false;
        $this->iterator->next();
    }

    /**
     * Return the key of the current element.
     *
     * @psalm-return Tk
     */
    public function key()
    {
        Psl\invariant($this->valid(), 'Invalid Iterator');
        $key = $this->iterator->key();
        if ($this->iterator instanceof Generator && !$this->cached) {
            $this->cached = true;
            $this->cache[] = [$key, $this->current()];
        }

        return $key;
    }

    /**
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        $valid = $this->iterator->valid();
        if (!$valid && $this->iterator instanceof Generator) {
            /** @psalm-var (Closure(): Generator<Tk, Tv, mixed, void>) $generator */
            $generator = function (): Generator {
                foreach ($this->cache as [$k, $v]) {
                    yield $k => $v;
                }
            };

            $this->iterator = new Iter\Iterator($generator());
        }

        return $valid;
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        if (!$this->iterator instanceof Generator) {
            $this->iterator->rewind();
        }
    }
}
