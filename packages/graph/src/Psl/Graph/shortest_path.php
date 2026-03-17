<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Finds the shortest path between two nodes with integer weights.
 *
 * For unweighted graphs (all edges have null weight), uses BFS.
 * For weighted graphs, uses Dijkstra's algorithm.
 *
 * Returns the path as a list of nodes, or null if no path exists.
 *
 * For non-integer weights (e.g., float), use shortest_path_by() with a custom
 * weight conversion function.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B', 1);
 *      $graph = Graph\add_edge($graph, 'B', 'C', 2);
 *      $graph = Graph\add_edge($graph, 'A', 'C', 5);
 *      Graph\shortest_path($graph, 'A', 'C') // ['A', 'B', 'C']
 *
 * @template TNode
 *
 * @param DirectedGraph<TNode, int>|UndirectedGraph<TNode, int> $graph
 * @param TNode $from
 * @param TNode $to
 *
 * @return list<TNode>|null
 *
 * @pure
 */
function shortest_path(DirectedGraph|UndirectedGraph $graph, mixed $from, mixed $to): null|array
{
    return shortest_path_by($graph, $from, $to, static fn(int $weight): int => $weight);
}
