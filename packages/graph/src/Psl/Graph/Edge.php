<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Represents an edge in a graph.
 *
 * @api
 */
final readonly class Edge<TNode = mixed, TWeight = mixed>
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
