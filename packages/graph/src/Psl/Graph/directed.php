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
 * @pure
 *
 * @api
 */
function directed<TNode, TWeight>(): DirectedGraph<TNode, TWeight>
{
    return new DirectedGraph::<TNode, TWeight>([]);
}
