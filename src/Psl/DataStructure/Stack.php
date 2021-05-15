<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

/**
 * An basic implementation of a stack data structure ( LIFO ).
 *
 * @template T
 *
 * @implements StackInterface<T>
 */
final class Stack implements StackInterface
{
    /**
     * @var list<T> $items
     */
    private array $items = [];

    /**
     * Adds an item to the stack.
     *
     * @param T $item
     */
    public function push(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Retrieves, but does remove, the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function peek(): mixed
    {
        return Iter\last($this->items);
    }

    /**
     * Retrieves and removes the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function pull(): mixed
    {
        if (0 === $this->count()) {
            return null;
        }

        /** @psalm-suppress MissingThrowsDocblock - the stack is not empty. */
        return $this->pop();
    }

    /**
     * Retrieve and removes the most recently added item that was not yet removed.
     *
     * @throws Psl\Exception\InvariantViolationException If the stack is empty.
     *
     * @return T
     */
    public function pop(): mixed
    {
        Psl\invariant(0 !== ($i = $this->count()), 'Cannot pop an item from an empty Stack.');

        $tail = $this->items[$i - 1];
        $this->items = Vec\values(Dict\take($this->items, $this->count() - 1));

        return $tail;
    }

    /**
     * Count the items in the stack.
     */
    public function count(): int
    {
        return Iter\count($this->items);
    }
}
