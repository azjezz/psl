<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Checks if the graph contains a cycle.
 *
 * For directed graphs, uses DFS with recursion stack.
 * For undirected graphs, uses DFS with parent tracking.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *      $graph = Graph\add_edge($graph, 'B', 'C');
 *      $graph = Graph\add_edge($graph, 'C', 'A');
 *      Graph\has_cycle($graph) // true
 *
 * @param GraphInterface<TNode, TWeight> $graph
 *
 * @pure
 *
 * @api
 */
function has_cycle<TNode, TWeight>(GraphInterface<TNode, TWeight> $graph): bool
{
    return $graph->hasCycle();
}
