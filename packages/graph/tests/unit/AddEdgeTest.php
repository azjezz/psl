<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class AddEdgeTest extends TestCase
{
    public function testAddEdgeToDirectedGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertFalse($graph->hasEdge('B', 'A'));
    }

    public function testAddEdgeToUndirectedGraph(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_edge($graph, 'A', 'B');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertTrue($graph->hasEdge('B', 'A'));
    }

    public function testAddEdgeAutomaticallyAddsNodes(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');

        static::assertTrue($graph->hasNode('A'));
        static::assertTrue($graph->hasNode('B'));
    }

    public function testAddWeightedEdge(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B', 5);

        $edges = $graph->getEdgesFrom('A');
        static::assertCount(1, $edges);
        static::assertSame('B', $edges[0]->to);
        static::assertSame(5, $edges[0]->weight);
    }

    public function testAddMultipleEdges(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'A', 'C');
        $graph = Graph\add_edge($graph, 'B', 'C');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertTrue($graph->hasEdge('A', 'C'));
        static::assertTrue($graph->hasEdge('B', 'C'));
    }

    public function testAddEdgeToExistingNodes(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');
        $graph = Graph\add_node($graph, 'B');
        $graph = Graph\add_edge($graph, 'A', 'B');

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertCount(2, $graph->getNodes());
    }
}
