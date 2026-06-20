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
 * @return ($graph is DirectedGraph<TNode, TWeight> ? DirectedGraph<TNode, TWeight> : UndirectedGraph<TNode, TWeight>)
 *
 * @pure
 *
 * @api
 */
function add_edge<TNode, TWeight>(
    DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph,
    TNode $from,
    TNode $to,
    TWeight|null $weight = null,
): DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> {
    // Ensure both nodes exist
    if (!$graph->hasNode($from)) {
        $graph = namespace\add_node::<TNode, TWeight>($graph, $from);
    }

    if (!$graph->hasNode($to)) {
        $graph = namespace\add_node::<TNode, TWeight>($graph, $to);
    }

    $edge = new Edge::<TNode, TWeight>($to, $weight);

    // Add edge from -> to
    $graph = $graph->withEdge($from, $edge);

    // For undirected graphs, also add edge to -> from
    if ($graph instanceof UndirectedGraph) {
        $graph = $graph->withEdge($to, new Edge::<TNode, TWeight>($from, $weight));
    }

    return $graph;
}
