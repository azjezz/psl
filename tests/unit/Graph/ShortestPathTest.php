<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Graph;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class ShortestPathTest extends TestCase
{
    public function testShortestPathBetweenSameNode(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertSame(['A'], Graph\shortest_path($graph, 'A', 'A'));
    }

    public function testShortestPathWhenFromNodeDoesNotExist(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertNull(Graph\shortest_path($graph, 'B', 'A'));
    }

    public function testShortestPathWhenToNodeDoesNotExist(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertNull(Graph\shortest_path($graph, 'A', 'B'));
    }

    public function testShortestPathInUnweightedGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'A', 'C');

        static::assertSame(['A', 'C'], Graph\shortest_path($graph, 'A', 'C'));
    }

    public function testShortestPathWhenNoPathExists(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_node($graph, 'C');

        static::assertNull(Graph\shortest_path($graph, 'A', 'C'));
    }

    public function testShortestPathInWeightedGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B', 1);
        $graph = Graph\add_edge($graph, 'B', 'C', 2);
        $graph = Graph\add_edge($graph, 'A', 'C', 5);

        static::assertSame(['A', 'B', 'C'], Graph\shortest_path($graph, 'A', 'C'));
    }

    public function testShortestPathWithLongerButCheaperRoute(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B', 1);
        $graph = Graph\add_edge($graph, 'B', 'C', 1);
        $graph = Graph\add_edge($graph, 'C', 'D', 1);
        $graph = Graph\add_edge($graph, 'A', 'D', 10);

        static::assertSame(['A', 'B', 'C', 'D'], Graph\shortest_path($graph, 'A', 'D'));
    }

    public function testShortestPathInUndirectedGraph(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_edge($graph, 'A', 'B', 1);
        $graph = Graph\add_edge($graph, 'B', 'C', 1);

        $path = Graph\shortest_path($graph, 'A', 'C');
        static::assertSame(['A', 'B', 'C'], $path);
    }

    public function testShortestPathWithComplexGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B', 4);
        $graph = Graph\add_edge($graph, 'A', 'C', 2);
        $graph = Graph\add_edge($graph, 'B', 'D', 3);
        $graph = Graph\add_edge($graph, 'C', 'B', 1);
        $graph = Graph\add_edge($graph, 'C', 'D', 5);

        static::assertSame(['A', 'C', 'B', 'D'], Graph\shortest_path($graph, 'A', 'D'));
    }
}
