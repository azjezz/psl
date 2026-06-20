<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class AddEdgeTest extends TestCase
{
    public function testAddEdgeToDirectedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertFalse($graph->hasEdge('B', 'A'));
    }

    public function testAddEdgeToUndirectedGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertTrue($graph->hasEdge('B', 'A'));
    }

    public function testAddEdgeAutomaticallyAddsNodes(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');

        static::assertTrue($graph->hasNode('A'));
        static::assertTrue($graph->hasNode('B'));
    }

    public function testAddWeightedEdge(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B', 5);

        $edges = $graph->getEdgesFrom('A');
        static::assertCount(1, $edges);
        static::assertSame('B', $edges[0]->to);
        static::assertSame(5, $edges[0]->weight);
    }

    public function testAddMultipleEdges(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertTrue($graph->hasEdge('A', 'C'));
        static::assertTrue($graph->hasEdge('B', 'C'));
    }

    public function testAddEdgeToExistingNodes(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');
        $graph = Graph\add_node::<string, int>($graph, 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertCount(2, $graph->getNodes());
    }
}
