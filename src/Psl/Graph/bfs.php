<?php

declare(strict_types=1);

namespace Psl\Graph;

use Psl\DataStructure\Queue;

use function Psl\Graph\Internal\get_node_key;

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
 * @template TNode
 * @template TWeight
 *
 * @param DirectedGraph<TNode, TWeight>|UndirectedGraph<TNode, TWeight> $graph
 * @param TNode $start
 *
 * @return list<TNode>
 *
 * @pure
 */
function bfs(DirectedGraph|UndirectedGraph $graph, mixed $start): array
{
    if (!$graph->hasNode($start)) {
        return [];
    }

    $visited = [];
    $result = [];
    /**
     * @var Queue<TNode> $queue
     * @mago-expect analysis:redundant-docblock-type
     */
    $queue = new Queue();
    $queue->enqueue($start);
    $visited[get_node_key($start)] = true;

    while ($queue->count() !== 0) {
        $node = $queue->dequeue();
        $result[] = $node;

        foreach (neighbors($graph, $node) as $neighbor) {
            $key = get_node_key($neighbor);
            if (!isset($visited[$key])) {
                $visited[$key] = true;
                $queue->enqueue($neighbor);
            }
        }
    }

    return $result;
}
