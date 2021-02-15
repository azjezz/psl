<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
use Psl\Arr;
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
     * @psalm-var list<T> $items
     */
    private array $items = [];

    /**
     * Adds an item to the stack.
     *
     * @psalm-param T $item
     */
    public function push($item): void
    {
        $this->items[] = $item;
    }

    /**
     * Retrieves, but does remove, the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function peek()
    {
        return Arr\last($this->items);
    }

    /**
     * Retrieves and removes the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function pull()
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
     * @psalm-return T
     *
     * @throws Psl\Exception\InvariantViolationException If the stack is empty.
     */
    public function pop()
    {
        Psl\invariant(0 !== $this->count(), 'Cannot pop an item from an empty Stack.');

        $tail = Arr\lastx($this->items);
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
