<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class NeighborsTest extends TestCase
{
    public function testNeighborsOfNodeWithNoEdges(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame([], Graph\neighbors::<string, int>($graph, 'A'));
    }

    public function testNeighborsOfNodeWithEdges(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'D');

        static::assertSame(['B', 'C', 'D'], Graph\neighbors::<string, int>($graph, 'A'));
    }

    public function testNeighborsInUndirectedGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');

        $neighbors = Graph\neighbors::<string, int>($graph, 'A');
        static::assertCount(2, $neighbors);
        static::assertContains('B', $neighbors);
        static::assertContains('C', $neighbors);

        $neighborsB = Graph\neighbors::<string, int>($graph, 'B');
        static::assertContains('A', $neighborsB);
    }

    public function testNeighborsOfNonExistentNode(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame([], Graph\neighbors::<string, int>($graph, 'B'));
    }
}
