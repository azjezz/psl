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
 * @return UndirectedGraph<TNode, TWeight>
 *
 * @pure
 *
 * @api
 */
function undirected<TNode = mixed, TWeight = mixed>(): UndirectedGraph<TNode, TWeight>
{
    return new UndirectedGraph([]);
}
