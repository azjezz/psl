<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Adds an edge to the graph.
 *
 * For directed graphs, adds an edge from $from to $to.
 * For undirected graphs, adds edges in both directions.
 *
 * Both nodes are automatically added if they don't exist.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *      $graph = Graph\add_edge($graph, 'A', 'C', 5); // weighted edge
 *
 * @template TNode
 * @template TWeight
 *
 * @param DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph
 * @param TNode $from
 * @param TNode $to
 * @param TWeight|null $weight
 *
 * @return ($graph is DirectedGraph<TNode, TWeight> ? DirectedGraph<TNode, TWeight> : UndirectedGraph<TNode, TWeight>)
 *
 * @pure
 */
function add_edge(
    DirectedGraph|UndirectedGraph $graph,
    mixed $from,
    mixed $to,
    mixed $weight = null,
): DirectedGraph|UndirectedGraph {
    // Ensure both nodes exist
    if (!$graph->hasNode($from)) {
        $graph = add_node($graph, $from);
    }

    if (!$graph->hasNode($to)) {
        $graph = add_node($graph, $to);
    }

    $edge = new Edge($to, $weight);

    // Add edge from -> to
    $graph = $graph->withEdge($from, $edge);

    // For undirected graphs, also add edge to -> from
    if ($graph instanceof UndirectedGraph) {
        $graph = $graph->withEdge($to, new Edge($from, $weight));
    }

    return $graph;
}
