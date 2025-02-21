<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use function array_pop;
use function count;

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
     * Provides a default instance of the {@see Stack}.
     *
     * @return static A new instance of {@see Stack}, devoid of any items.
     *
     * @pure
     */
    public static function default(): static
    {
        return new self();
    }

    /**
     * Adds an item to the stack.
     *
     * @param T $item
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function push(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Retrieves, but does remove, the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     *
     * @return null|T
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function peek(): mixed
    {
        $items = $this->items;

        return array_pop($items);
    }

    /**
     * Retrieves and removes the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     *
     * @return null|T
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function pull(): mixed
    {
        return array_pop($this->items);
    }

    /**
     * Retrieve and removes the most recently added item that was not yet removed.
     *
     * @throws Exception\UnderflowException If the stack is empty.
     *
     * @return T
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function pop(): mixed
    {
        if ([] === $this->items) {
            throw new Exception\UnderflowException('Cannot pop an item from an empty stack.');
        }

        /** @var T */
        return array_pop($this->items);
    }

    /**
     * Count the items in the stack.
     *
     * @return int<0, max>
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function count(): int
    {
        return count($this->items);
    }
}
