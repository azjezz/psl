<?php

declare(strict_types=1);

namespace Psl\Graph;

/**
 * Common interface for graph implementations.
 *
 * @template TNode
 * @template TWeight
 *
 * @inheritors DirectedGraph|UndirectedGraph
 */
interface GraphInterface
{
    /**
     * Returns all nodes in the graph.
     *
     * @return list<TNode>
     *
     * @pure
     */
    public function getNodes(): array;

    /**
     * Returns all edges from a given node.
     *
     * @param TNode $from
     *
     * @return list<Edge<TNode, TWeight>>
     *
     * @pure
     */
    public function getEdgesFrom(mixed $from): array;

    /**
     * Checks if a node exists in the graph.
     *
     * @param TNode $node
     *
     * @pure
     */
    public function hasNode(mixed $node): bool;

    /**
     * Checks if the graph contains a cycle.
     *
     * For directed graphs, a cycle exists if there's a path from a node back to itself.
     * For undirected graphs, a cycle exists if there's a path between two nodes that doesn't backtrack.
     *
     * @pure
     */
    public function hasCycle(): bool;
}
