<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class NeighborsTest extends TestCase
{
    public function testNeighborsOfNodeWithNoEdges(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertSame([], Graph\neighbors($graph, 'A'));
    }

    public function testNeighborsOfNodeWithEdges(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'A', 'C');
        $graph = Graph\add_edge($graph, 'A', 'D');

        static::assertSame(['B', 'C', 'D'], Graph\neighbors($graph, 'A'));
    }

    public function testNeighborsInUndirectedGraph(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'A', 'C');

        $neighbors = Graph\neighbors($graph, 'A');
        static::assertCount(2, $neighbors);
        static::assertContains('B', $neighbors);
        static::assertContains('C', $neighbors);

        $neighborsB = Graph\neighbors($graph, 'B');
        static::assertContains('A', $neighborsB);
    }

    public function testNeighborsOfNonExistentNode(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertSame([], Graph\neighbors($graph, 'B'));
    }
}
