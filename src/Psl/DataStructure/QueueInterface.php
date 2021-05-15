<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Countable;
use Psl;

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
     * @throws Psl\Exception\InvariantViolationException If the Queue is invalid.
     *
     * @return T
     */
    public function dequeue(): mixed;

    /**
     * Count the nodes in the queue.
     */
    public function count(): int;
}
