<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Represents an edge in a graph.
 *
 * @api
 */
final readonly class Edge<TNode, TWeight>
{
    public function __construct(
        public TNode $to,
        public TWeight|null $weight = null,
    ) {}
}
