<?php

declare(strict_types=1);

namespace Psl\Graph;

use Psl\DataStructure\Stack;

use function count;
use function Psl\Graph\Internal\get_node_key;

/**
 * Performs depth-first search starting from a given node.
 *
 * Returns nodes in the order they are visited.
 *
 * Example:
 *
 *      $graph = Graph\directed();
 *      $graph = Graph\add_edge($graph, 'A', 'B');
 *      $graph = Graph\add_edge($graph, 'A', 'C');
 *      $graph = Graph\add_edge($graph, 'B', 'D');
 *      Graph\dfs($graph, 'A') // ['A', 'B', 'D', 'C']
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
function dfs(DirectedGraph|UndirectedGraph $graph, mixed $start): array
{
    if (!$graph->hasNode($start)) {
        return [];
    }

    $visited = [];
    $result = [];
    $stack = new Stack();
    $stack->push($start);

    while ($stack->count() !== 0) {
        $node = $stack->pop();
        $key = get_node_key($node);

        if (isset($visited[$key])) {
            continue;
        }

        $visited[$key] = true;
        $result[] = $node;

        // Push neighbors in reverse order to maintain left-to-right traversal
        $neighborsList = namespace\neighbors($graph, $node);
        for ($i = count($neighborsList) - 1; $i >= 0; $i--) {
            $neighborKey = get_node_key($neighborsList[$i]);
            if (!isset($visited[$neighborKey])) {
                $stack->push($neighborsList[$i]);
            }
        }
    }

    return $result;
}
