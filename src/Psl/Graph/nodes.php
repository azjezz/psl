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
 * @template TNode
 * @template TWeight
 *
 * @param GraphInterface<TNode, TWeight> $graph
 *
 * @return list<TNode>
 *
 * @pure
 */
function nodes(GraphInterface $graph): array
{
    return $graph->getNodes();
}
