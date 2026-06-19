<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Returns all nodes in the graph.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_node($graph, 'A');
 *      $graph = Graph\add_node($graph, 'B');
 *      Graph\nodes($graph) // ['A', 'B']
 *
 * @param GraphInterface<TNode, TWeight> $graph
 *
 * @return list<TNode>
 *
 * @pure
 *
 * @api
 */
function nodes<TNode = mixed, TWeight = mixed>(GraphInterface<TNode, TWeight> $graph): array
{
    return $graph->getNodes();
}
