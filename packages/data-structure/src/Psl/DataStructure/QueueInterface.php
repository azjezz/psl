<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Countable;
use Override;
use Psl\Default\DefaultInterface;

/**
 * An interface representing a queue data structure ( FIFO ).
 *
 * @see https://en.wikipedia.org/wiki/FIFO_(computing_and_electronics)
 *
 * @api
 */
interface QueueInterface<T> extends Countable, DefaultInterface
{
    /**
     * Adds a node to the queue.
     */
    public function enqueue(T $node): void;

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     */
    public function peek(): null|T;

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     */
    public function pull(): null|T;

    /**
     * Retrieves and removes the node at the head of this queue.
     *
     * @throws Exception\UnderflowException If the queue is empty.
     */
    public function dequeue(): T;

    /**
     * Count the nodes in the queue.
     *
     * @return int<0, max>
     */
    #[Override]
    public function count(): int;
}
