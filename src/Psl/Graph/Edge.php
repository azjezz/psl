<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Represents an edge in a graph.
 *
 * @template TNode
 * @template TWeight
 */
final readonly class Edge
{
    /**
     * @param TNode $to
     * @param TWeight|null $weight
     */
    public function __construct(
        public mixed $to,
        public mixed $weight = null,
    ) {}
}
