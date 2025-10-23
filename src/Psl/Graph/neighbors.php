<?php

declare(strict_types=1);

namespace Psl\Graph;

use Psl\Vec;

/**
 * Returns all neighbor nodes of a given node.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *      $graph = Graph\add_edge($graph, 'A', 'C');
 *      Graph\neighbors($graph, 'A') // ['B', 'C']
 *
 * @template TNode
 * @template TWeight
 *
 * @param DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph
 * @param TNode $node
 *
 * @return list<TNode>
 *
 * @pure
 */
function neighbors(DirectedGraph|UndirectedGraph $graph, mixed $node): array
{
    $edges = $graph->getEdgesFrom($node);

    return Vec\map(
        $edges,
        /**
         * @param Edge<TNode, TWeight> $edge
         *
         * @return TNode
         */
        static fn(Edge $edge): mixed => $edge->to,
    );
}
