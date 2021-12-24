<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Countable;

/**
 * An interface representing a queue data structure ( FIFO ).
 *
 * @template T
 *
 * @see https://en.wikipedia.org/wiki/FIFO_(computing_and_electronics)
 */
interface QueueInterface extends Countable
{
    /**
     * Adds a node to the queue.
     *
     * @param T $node
     */
    public function enqueue(mixed $node): void;

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function peek(): mixed;

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function pull(): mixed;

    /**
     * Retrieves and removes the node at the head of this queue.
     *
     * @throws Exception\UnderflowException If the queue is empty.
     *
     * @return T
     */
    public function dequeue(): mixed;

    /**
     * Count the nodes in the queue.
     *
     * @return int<0, max>
     */
    public function count(): int;
}
