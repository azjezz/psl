<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
use Psl\Arr;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

/**
 * A basic implementation of a queue data structure ( FIFO ).
 *
 * @psalm-template T
 *
 * @implements QueueInterface<T>
 */
final class Queue implements QueueInterface
{
    /**
     * @psalm-var  list<T>
     */
    private array $queue = [];

    /**
     * Adds a node to the queue.
     *
     * @psalm-param T $node
     */
    public function enqueue($node): void
    {
        $this->queue[] = $node;
    }

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function peek()
    {
        return Arr\first($this->queue);
    }

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-return null|T
     */
    public function pull()
    {
        if (0 === $this->count()) {
            return null;
        }

        /** @psalm-suppress MissingThrowsDocblock - we are sure that the queue is not empty. */
        return $this->dequeue();
    }

    /**
     * Dequeues a node from the queue.
     *
     * @psalm-return T
     *
     * @throws Psl\Exception\InvariantViolationException If the Queue is invalid.
     */
    public function dequeue()
    {
        Psl\invariant(0 !== $this->count(), 'Cannot dequeue a node from an empty Queue.');

        $node = Arr\firstx($this->queue);
        $this->queue = Vec\values(Dict\drop($this->queue, 1));

        return $node;
    }

    /**
     * Count the nodes in the queue.
     */
    public function count(): int
    {
        return Iter\count($this->queue);
    }
}
