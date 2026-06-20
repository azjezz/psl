<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Override;

/**
 * @api
 */
interface PriorityQueueInterface<T> extends QueueInterface<T>
{
    /**
     * Adds a node to the queue.
     */
    #[Override]
    public function enqueue(T $node, int $priority = 0): void;
}
