<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Creates an empty directed graph.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_node($graph, 'A');
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *
 * @template TNode
 * @template TWeight
 *
 * @return DirectedGraph<TNode, TWeight>
 *
 * @pure
 */
function directed(): DirectedGraph
{
    return new DirectedGraph([]);
}
