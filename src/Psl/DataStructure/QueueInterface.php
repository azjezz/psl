<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
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
     * @psalm-param T $node
     */
    public function enqueue($node): void;

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function peek();

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function pull();

    /**
     * Retrieves and removes the node at the head of this queue.
     *
     * @psalm-return T
     *
     * @throws Psl\Exception\InvariantViolationException If the Queue is invalid.
     */
    public function dequeue();

    /**
     * Count the nodes in the queue.
     */
    public function count(): int;
}
