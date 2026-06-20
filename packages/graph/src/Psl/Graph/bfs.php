<?php

declare(strict_types=1);

namespace Psl\Graph;

use Psl\DataStructure\Queue;

/**
 * Performs breadth-first search starting from a given node.
 *
 * Returns nodes in the order they are visited.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *      $graph = Graph\add_edge($graph, 'A', 'C');
 *      $graph = Graph\add_edge($graph, 'B', 'D');
 *      Graph\bfs($graph, 'A') // ['A', 'B', 'C', 'D']
 *
 * @return list<TNode>
 *
 * @pure
 *
 * @api
 */
function bfs<TNode, TWeight>(
    DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph,
    TNode $start,
): array {
    if (!$graph->hasNode($start)) {
        return [];
    }

    $visited = [];
    $result = [];
    $queue = new Queue::<TNode>();
    $queue->enqueue($start);
    $visited[Internal\get_node_key($start)] = true;

    while ($queue->count() !== 0) {
        $node = $queue->dequeue();
        $result[] = $node;

        foreach (namespace\neighbors::<TNode, TWeight>($graph, $node) as $neighbor) {
            $key = Internal\get_node_key($neighbor);
            if (!isset($visited[$key])) {
                $visited[$key] = true;
                $queue->enqueue($neighbor);
            }
        }
    }

    return $result;
}
