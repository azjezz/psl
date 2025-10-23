<?php

declare(strict_types=1);

namespace Psl\Graph;

use Psl\DataStructure\Queue;

use function Psl\Graph\Internal\get_node_key;

/**
 * Performs topological sort on a directed acyclic graph (DAG).
 *
 * Returns nodes in topological order, where for every directed edge (u, v), u comes before v.
 * Returns null if the graph contains a cycle.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *      $graph = Graph\add_edge($graph, 'A', 'C');
 *      $graph = Graph\add_edge($graph, 'B', 'D');
 *      $graph = Graph\add_edge($graph, 'C', 'D');
 *      Graph\topological_sort($graph) // ['A', 'B', 'C', 'D'] or ['A', 'C', 'B', 'D']
 *
 * @template TNode
 * @template TWeight
 *
 * @param DirectedGraph<TNode, TWeight> $graph
 *
 * @return list<TNode>|null null if graph contains a cycle
 *
 * @pure
 */
function topological_sort(DirectedGraph $graph): null|array
{
    $allNodes = nodes($graph);
    $inDegree = [];

    // Initialize in-degree for all nodes
    foreach ($allNodes as $node) {
        $key = get_node_key($node);
        $inDegree[$key] = 0;
    }

    // Calculate in-degree for each node
    foreach ($allNodes as $node) {
        foreach (neighbors($graph, $node) as $neighbor) {
            $key = get_node_key($neighbor);
            $inDegree[$key]++;
        }
    }

    // Start with nodes that have no incoming edges
    /**
     * @var Queue<TNode> $queue
     * @mago-expect analysis:redundant-docblock-type
     */
    $queue = new Queue();
    foreach ($allNodes as $node) {
        $key = get_node_key($node);
        if ($inDegree[$key] === 0) {
            $queue->enqueue($node);
        }
    }

    $result = [];
    while ($queue->count() !== 0) {
        $node = $queue->dequeue();
        $result[] = $node;

        // Reduce in-degree for all neighbors
        foreach (neighbors($graph, $node) as $neighbor) {
            $key = get_node_key($neighbor);
            $inDegree[$key]--;
            if ($inDegree[$key] === 0) {
                $queue->enqueue($neighbor);
            }
        }
    }

    // If we didn't visit all nodes, there's a cycle
    if (count($result) !== count($allNodes)) {
        return null;
    }

    return $result;
}
