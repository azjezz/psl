<?php

declare(strict_types=1);

namespace Psl\Iter;

use Countable;
use Generator;
use Psl;
use SeekableIterator;

use function array_key_exists;

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
     *  Whether or not the current value/key pair has been added to the local entries.
     */
    private bool $saved = true;

    /**
     * Current cursor position for the local entries.
     */
    private int $position = 0;

    /**
     * The size of the generator.
     *
     * @var int<0, max>
     */
    private ?int $count = null;

    /**
     * @param Generator<Tk, Tv, mixed, mixed> $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Create an iterator from a factory.
     *
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
     * Create an iterator from an iterable.
     *
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
        $factory = static fn(): Generator => yield from $iterable;

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
        $this->save();

        return $this->entries[$this->position][1];
    }

    /**
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        if (array_key_exists($this->position, $this->entries)) {
            return true;
        }

        if (null !== $this->generator && $this->generator->valid()) {
            return true;
        }

        $this->generator = null;
        return false;
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    private function save(): void
    {
        if ($this->generator) {
            if ($this->entries === []) {
                $this->saved = false;
            }

            if (!$this->saved && $this->generator->valid()) {
                $this->saved = true;
                $this->entries[] = [$this->generator->key(), $this->generator->current()];
            }
        }
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
        $this->save();

        return $this->entries[$this->position][0];
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Seek to the given position.
     *
     * @param int<0, max> $position
     *
     * @throws Psl\Exception\InvariantViolationException If $position is out-of-bounds.
     */
    public function seek(int $position): void
    {
        if ($position <= $this->position) {
            $this->position = $position;
            return;
        }

        if ($this->generator) {
            do {
                $this->save();
                $this->next();
                /** @psalm-suppress PossiblyNullReference - ->next() and ->save() don't mutate ->generator. */
                Psl\invariant($this->generator->valid(), 'Position is out-of-bounds.');
            } while ($this->position < $position);

            return;
        }

        Psl\invariant($position < $this->count(), 'Position is out-of-bounds.');

        $this->position = $position;
    }

    /**
     * Move forward to the next element.
     */
    public function next(): void
    {
        $this->position++;

        if (array_key_exists($this->position, $this->entries) || null === $this->generator || !$this->generator->valid()) {
            return;
        }

        $this->generator->next();
        $this->saved = false;
    }

    /**
     * @return int<0, max>
     *
     * @psalm-suppress PossiblyNullReference
     */
    public function count(): int
    {
        if ($this->generator) {
            $previous = $this->position;
            do {
                $this->save();
                $this->next();
            } while ($this->generator->valid());
            $this->position = $previous;

            $this->generator = null;
        }

        if (null !== $this->count) {
            return $this->count;
        }

        /** @var int<0, max> */
        $this->count = count($this->entries);

        return $this->count;
    }
}
