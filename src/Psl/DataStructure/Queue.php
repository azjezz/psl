<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

/**
 * A basic implementation of a queue data structure ( FIFO ).
 *
 * @template T
 *
 * @implements QueueInterface<T>
 */
final class Queue implements QueueInterface
{
    /**
     * @var list<T>
     */
    private array $queue = [];

    /**
     * Adds a node to the queue.
     *
     * @param T $node
     */
    public function enqueue(mixed $node): void
    {
        $this->queue[] = $node;
    }

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function peek(): mixed
    {
        return Iter\first($this->queue);
    }

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function pull(): mixed
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
     * @throws Psl\Exception\InvariantViolationException If the Queue is invalid.
     *
     * @return T
     */
    public function dequeue(): mixed
    {
        Psl\invariant(0 !== $this->count(), 'Cannot dequeue a node from an empty Queue.');

        $node = $this->queue[0];
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
