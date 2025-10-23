<?php

declare(strict_types=1);

namespace Psl\Graph;

use Psl\DataStructure\Queue;

use function Psl\Graph\Internal\get_node_key;

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
 * @template TNode
 * @template TWeight
 *
 * @param DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph
 * @param TNode $from
 * @param TNode $to
 *
 * @pure
 */
function has_path(DirectedGraph|UndirectedGraph $graph, mixed $from, mixed $to): bool
{
    if (!$graph->hasNode($from) || !$graph->hasNode($to)) {
        return false;
    }

    if ($from === $to) {
        return true;
    }

    $visited = [];
    /**
     * @var Queue<TNode> $queue
     * @mago-expect analysis:redundant-docblock-type
     */
    $queue = new Queue();
    $queue->enqueue($from);
    $visited[get_node_key($from)] = true;

    while ($queue->count() !== 0) {
        $node = $queue->dequeue();

        foreach (neighbors($graph, $node) as $neighbor) {
            if ($neighbor === $to) {
                return true;
            }

            $key = get_node_key($neighbor);
            if (!isset($visited[$key])) {
                $visited[$key] = true;
                $queue->enqueue($neighbor);
            }
        }
    }

    return false;
}
