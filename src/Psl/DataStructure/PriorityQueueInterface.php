<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl;
use Countable;

/**
 * @template T
 *
 * @extends QueueInterface<T>
 */
interface PriorityQueueInterface extends QueueInterface
{
    /**
     * Adds a node to the queue.
     *
     * @psalm-param T $node
     */
    public function enqueue($node, int $priority = 0): void;
}
