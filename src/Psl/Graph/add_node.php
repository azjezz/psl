<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Adds a node to the graph.
 *
 * Returns a new graph with the node added. If the node already exists, returns the original graph.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_node($graph, 'A');
 *      $graph = Graph\add_node($graph, 'B');
 *
 * @template TNode
 * @template TWeight
 *
 * @param DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph
 * @param TNode $node
 *
 * @return ($graph is DirectedGraph<TNode, TWeight> ? DirectedGraph<TNode, TWeight> : UndirectedGraph<TNode, TWeight>)
 *
 * @pure
 */
function add_node(DirectedGraph|UndirectedGraph $graph, mixed $node): DirectedGraph|UndirectedGraph
{
    return $graph->withNode($node);
}
