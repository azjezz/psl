<?php

declare(strict_types=1);

namespace Psl\Iter;

use Countable;
use Generator;
use Psl;
use SeekableIterator;

/**
 * @template   Tk
 * @template   Tv
 *
 * @implements SeekableIterator<Tk, Tv>
 */
final class Iterator implements Countable, SeekableIterator
{
    /**
     * @var Generator<Tk, Tv, mixed, mixed>
     */
    private ?Generator $generator;

    /**
     * @var array<int, array{0: Tk, 1: Tv}>
     */
    private array $entries = [];

    /**
     * Whether or not the current value/key pair has been added to the local entries.
     */
    private bool $saved = false;

    /**
     * Current cursor position for the local entries.
     */
    private int $position = 0;

    /**
     * @param Generator<Tk, Tv, mixed, mixed> $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @template Tsk
     * @template Tsv
     *
     * @param callable(): iterable<Tsk, Tsv> $factory
     *
     * @return Iterator<Tsk, Tsv>
     */
    public static function from(callable $factory): Iterator
    {
        return self::create($factory());
    }

    /**
     * @template Tsk
     * @template Tsv
     *
     * @param iterable<Tsk, Tsv> $iterable
     *
     * @return Iterator<Tsk, Tsv>
     */
    public static function create(iterable $iterable): Iterator
    {
        if ($iterable instanceof Generator) {
            return new self($iterable);
        }

        /**
         * @var (callable(): Generator<Tsk, Tsv, mixed, void>) $factory
         */
        $factory = static fn (): Generator => yield from $iterable;

        return new self($factory());
    }

    /**
     * Return the current element.
     *
     * @throws Psl\Exception\InvariantViolationException If the iterator is invalid.
     *
     * @return Tv
     */
    public function current(): mixed
    {
        Psl\invariant($this->valid(), 'The Iterator is invalid.');
        if (!contains_key($this->entries, $this->position)) {
            $this->progress();
        }

        return $this->entries[$this->position][1];
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        $this->position++;
        if (null === $this->generator || !$this->generator->valid()) {
            return;
        }

        if (contains_key($this->entries, $this->position + 1)) {
            return;
        }

        $this->progress();
        $this->saved = false;
        if ($this->generator) {
            $this->generator->next();
        }

        $this->progress();
    }

    /**
     * Return the key of the current element.
     *
     * @throws Psl\Exception\InvariantViolationException If the iterator is invalid.
     *
     * @return Tk
     */
    public function key(): mixed
    {
        Psl\invariant($this->valid(), 'The Iterator is invalid.');
        if (!contains_key($this->entries, $this->position)) {
            $this->progress();
        }

        return $this->entries[$this->position][0];
    }

    /**
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        if (contains_key($this->entries, $this->position)) {
            return true;
        }

        if (null === $this->generator) {
            return false;
        }

        if ($this->generator->valid()) {
            return true;
        }

        $this->generator = null;
        return false;
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        /** @psalm-suppress MissingThrowsDocblock - 0 is within bound. */
        $this->seek(0);
    }

    /**
     * @throws Psl\Exception\InvariantViolationException If $position is out-of-bounds.
     */
    public function seek(int $position): void
    {
        Psl\invariant($position >= 0, 'Position is out-of-bounds.');
        if (0 === $position || $position <= $this->position) {
            $this->position = $position;
            return;
        }

        if ($this->generator) {
            while ($this->position !== $position) {
                $this->next();
                Psl\invariant($this->valid(), 'Position is out-of-bounds.');
            }

            return;
        }

        Psl\invariant($position < $this->count(), 'Position is out-of-bounds.');

        $this->position = $position;
    }

    public function count(): int
    {
        if ($this->generator) {
            $this->exhaust();
        }

        return count($this->entries);
    }

    private function exhaust(): void
    {
        if ($this->generator) {
            if ($this->generator->valid()) {
                foreach ($this->generator as $key => $value) {
                    $this->entries[] = [$key, $value];
                }
            }

            $this->generator = null;
        }
    }

    /**
     * Save the current key and value to the local entries if the generator is still valid.
     */
    private function progress(): void
    {
        if ($this->generator && $this->generator->valid() && !$this->saved) {
            $this->entries[] = [$this->generator->key(), $this->generator->current()];
            $this->saved     = true;
        }
    }
}
