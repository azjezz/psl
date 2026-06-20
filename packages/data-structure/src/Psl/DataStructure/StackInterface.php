<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Countable;
use Override;

/**
 * An interface representing a stack data structure ( LIFO ).
 *
 * @see https://en.wikipedia.org/wiki/Stack_(abstract_data_type)
 *
 * @api
 */
interface StackInterface<T> extends Countable
{
    /**
     * Adds an item to the stack.
     */
    public function push(T $item): void;

    /**
     * Retrieves, but does remove, the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     */
    public function peek(): null|T;

    /**
     * Retrieves and removes the most recently added item that was not yet removed,
     * or returns null if this queue is empty.
     */
    public function pull(): null|T;

    /**
     * Retrieve and removes the most recently added item that was not yet removed.
     *
     * @throws Exception\UnderflowException If the stack is empty.
     */
    public function pop(): T;

    /**
     * Count the items in the stack.
     *
     * @return int<0, max>
     */
    #[Override]
    public function count(): int;
}
