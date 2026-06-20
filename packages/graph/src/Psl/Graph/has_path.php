<?php

declare(strict_types=1);

namespace Psl\Graph;

use Psl\DataStructure\Queue;

/**
 * Checks if there is a path from one node to another.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *      $graph = Graph\add_edge($graph, 'B', 'C');
 *      Graph\has_path($graph, 'A', 'C') // true
 *      Graph\has_path($graph, 'C', 'A') // false
 *
 * @pure
 *
 * @api
 */
function has_path<TNode, TWeight>(
    DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph,
    TNode $from,
    TNode $to,
): bool {
    if (!$graph->hasNode($from) || !$graph->hasNode($to)) {
        return false;
    }

    if ($from === $to) {
        return true;
    }

    $visited = [];
    $queue = new Queue::<TNode>();
    $queue->enqueue($from);
    $visited[Internal\get_node_key($from)] = true;

    while ($queue->count() !== 0) {
        $node = $queue->dequeue();

        foreach (namespace\neighbors::<TNode, TWeight>($graph, $node) as $neighbor) {
            if ($neighbor === $to) {
                return true;
            }

            $key = Internal\get_node_key($neighbor);
            if (!isset($visited[$key])) {
                $visited[$key] = true;
                $queue->enqueue($neighbor);
            }
        }
    }

    return false;
}
