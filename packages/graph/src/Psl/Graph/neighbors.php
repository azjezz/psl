<?php

declare(strict_types=1);

namespace Psl\Graph;

use function array_map;

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
 * @return list<TNode>
 *
 * @pure
 *
 * @api
 */
function neighbors<TNode, TWeight>(
    DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph,
    TNode $node,
): array {
    $edges = $graph->getEdgesFrom($node);

    return array_map(
        static fn(Edge<TNode, TWeight> $edge): TNode => $edge->to,
        $edges,
    );
}
