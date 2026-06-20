<?php

declare(strict_types=1);

namespace Psl\Graph;

use function array_values;

/**
 * Immutable directed graph using adjacency list representation.
 *
 * Supports any node type (scalars, objects, arrays, resources, etc.).
 *
 * @api
 */
final readonly class DirectedGraph<TNode, TWeight> implements GraphInterface<TNode, TWeight>
{
    /**
     * @param array<non-empty-string, TNode> $nodes Map from node key to node
     * @param array<non-empty-string, list<Edge<TNode, TWeight>>> $edges Map from node key to edges
     *
     * @internal Use Graph\directed() to create instances
     */
    public function __construct(
        private array $nodes = [],
        private array $edges = [],
    ) {}

    /**
     * Returns all nodes in the graph.
     *
     * @return list<TNode>
     *
     * @pure
     */
    public function getNodes(): array
    {
        return array_values($this->nodes);
    }

    /**
     * Returns all edges from a given node.
     *
     * @return list<Edge<TNode, TWeight>>
     *
     * @pure
     */
    public function getEdgesFrom(TNode $from): array
    {
        $key = Internal\get_node_key($from);
        return $this->edges[$key] ?? [];
    }

    /**
     * Checks if a node exists in the graph.
     *
     * @pure
     */
    public function hasNode(TNode $node): bool
    {
        $key = Internal\get_node_key($node);
        return isset($this->nodes[$key]);
    }

    /**
     * Checks if an edge exists from one node to another.
     *
     * @pure
     */
    public function hasEdge(TNode $from, TNode $to): bool
    {
        $key = Internal\get_node_key($from);
        if (!isset($this->edges[$key])) {
            return false;
        }

        foreach ($this->edges[$key] as $edge) {
            if ($edge->to !== $to) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns a new graph with the node added.
     *
     * @internal
     *
     * @pure
     */
    public function withNode(TNode $node): DirectedGraph<TNode, TWeight>
    {
        if ($this->hasNode($node)) {
            return $this;
        }

        $key = Internal\get_node_key($node);
        $nodes = $this->nodes;
        $edges = $this->edges;
        $nodes[$key] = $node;
        $edges[$key] = [];

        return new DirectedGraph::<TNode, TWeight>($nodes, $edges);
    }

    /**
     * Returns a new graph with the edge added.
     *
     * @internal
     *
     * @pure
     */
    public function withEdge(TNode $from, Edge<TNode, TWeight> $edge): DirectedGraph<TNode, TWeight>
    {
        $key = Internal\get_node_key($from);
        $edges = $this->edges;
        $edges[$key] ??= [];
        $edges[$key][] = $edge;

        return new DirectedGraph::<TNode, TWeight>($this->nodes, $edges);
    }

    /**
     * Checks if the graph contains a cycle.
     *
     * Uses DFS with recursion stack to detect back edges.
     * A cycle exists if we encounter a node that's in the current recursion stack.
     *
     * @pure
     */
    public function hasCycle(): bool
    {
        $visited = [];
        $recursionStack = [];

        $dfsCheck =
            function (TNode $node) use (&$visited, &$recursionStack, &$dfsCheck): bool {
                $key = Internal\get_node_key($node);
                $visited[$key] = true;
                $recursionStack[$key] = true;

                foreach ($this->getEdgesFrom($node) as $edge) {
                    $neighborKey = Internal\get_node_key($edge->to);

                    if (!isset($visited[$neighborKey])) {
                        if ($dfsCheck($edge->to)) {
                            return true;
                        }

                        continue;
                    }

                    if (isset($recursionStack[$neighborKey])) {
                        return true;
                    }
                }

                unset($recursionStack[$key]);

                return false;
            };

        foreach ($this->getNodes() as $node) {
            $key = Internal\get_node_key($node);
            if (!isset($visited[$key])) {
                if ($dfsCheck($node)) {
                    return true;
                }
            }
        }

        return false;
    }
}
