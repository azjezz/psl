<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Creates an empty undirected graph.
 *
 * Example:
 *
 *      $graph = Graph\undirected();
 *      $graph = Graph\add_node($graph, 'A');
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *
 * @pure
 *
 * @api
 */
function undirected<TNode, TWeight>(): UndirectedGraph<TNode, TWeight>
{
    return new UndirectedGraph::<TNode, TWeight>([]);
}
