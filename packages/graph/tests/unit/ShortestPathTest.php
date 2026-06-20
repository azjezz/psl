<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class ShortestPathTest extends TestCase
{
    public function testShortestPathBetweenSameNode(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame(['A'], Graph\shortest_path::<string>($graph, 'A', 'A'));
    }

    public function testShortestPathWhenFromNodeDoesNotExist(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertNull(Graph\shortest_path::<string>($graph, 'B', 'A'));
    }

    public function testShortestPathWhenToNodeDoesNotExist(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertNull(Graph\shortest_path::<string>($graph, 'A', 'B'));
    }

    public function testShortestPathInUnweightedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');

        static::assertSame(['A', 'C'], Graph\shortest_path::<string>($graph, 'A', 'C'));
    }

    public function testShortestPathWhenNoPathExists(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_node::<string, int>($graph, 'C');

        static::assertNull(Graph\shortest_path::<string>($graph, 'A', 'C'));
    }

    public function testShortestPathInWeightedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B', 1);
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C', 2);
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C', 5);

        static::assertSame(['A', 'B', 'C'], Graph\shortest_path::<string>($graph, 'A', 'C'));
    }

    public function testShortestPathWithLongerButCheaperRoute(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B', 1);
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C', 1);
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D', 1);
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'D', 10);

        static::assertSame(['A', 'B', 'C', 'D'], Graph\shortest_path::<string>($graph, 'A', 'D'));
    }

    public function testShortestPathInUndirectedGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B', 1);
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C', 1);

        $path = Graph\shortest_path::<string>($graph, 'A', 'C');
        static::assertSame(['A', 'B', 'C'], $path);
    }

    public function testShortestPathWithComplexGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B', 4);
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C', 2);
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D', 3);
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'B', 1);
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D', 5);

        static::assertSame(['A', 'C', 'B', 'D'], Graph\shortest_path::<string>($graph, 'A', 'D'));
    }
}
