<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use function array_shift;
use function count;

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
        return $this->queue[0] ?? null;
    }

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function pull(): mixed
    {
        return array_shift($this->queue);
    }

    /**
     * Dequeues a node from the queue.
     *
     * @throws Exception\UnderflowException If the queue is empty.
     *
     * @return T
     */
    public function dequeue(): mixed
    {
        if ([] === $this->queue) {
            throw new Exception\UnderflowException('Cannot dequeue a node from an empty queue.');
        }

        /** @var T */
        return array_shift($this->queue);
    }

    /**
     * Count the nodes in the queue.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->queue);
    }
}
