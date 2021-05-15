<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
use Psl\Dict;
use Psl\Iter;
use Psl\Math;
use Psl\Vec;

/**
 * @template T
 *
 * @implements PriorityQueueInterface<T>
 */
final class PriorityQueue implements PriorityQueueInterface
{
    /**
     * @var array<int, list<T>>
     */
    private array $queue = [];

    /**
     * Adds a node to the queue.
     *
     * @param T $node
     */
    public function enqueue(mixed $node, int $priority = 0): void
    {
        $nodes = $this->queue[$priority] ?? [];
        $nodes[] = $node;

        $this->queue[$priority] = $nodes;
    }

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function peek(): mixed
    {
        if (0 === $this->count()) {
            return null;
        }

        $keys = Vec\keys($this->queue);

        // Retrieve the highest priority.
        $priority = Math\max($keys) ?? 0;

        // Retrieve the list of nodes with the priority `$priority`.
        $nodes = $this->queue[$priority] ?? [];

        // Retrieve the first node of the list.
        return Iter\first($nodes);
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

        /** @psalm-suppress MissingThrowsDocblock - the queue is not empty */
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

        /**
         * Peeking into a non-empty queue always results in a value.
         *
         * @var T $node
         */
        $node = $this->peek();

        $this->drop();

        return $node;
    }

    private function drop(): void
    {
        /**
         * Retrieve the highest priority.
         *
         * @var int $priority
         */
        $priority = Math\max(Vec\keys($this->queue));

        /**
         * Retrieve the list of nodes with the priority `$priority`.
         */
        $nodes = $this->queue[$priority];

        // If the list contained only this node,
        // remove the list of nodes with priority `$priority`.
        if (1 === Iter\count($nodes)) {
            unset($this->queue[$priority]);
        } else {
            /**
             * otherwise, drop the first node.
             *
             * @psalm-suppress MissingThrowsDocblock
             */
            $this->queue[$priority] = Vec\values(Dict\drop($nodes, 1));
        }
    }

    /**
     * Count the nodes in the queue.
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->queue as $_priority => $list) {
            $count += Iter\count($list);
        }

        return $count;
    }
}
