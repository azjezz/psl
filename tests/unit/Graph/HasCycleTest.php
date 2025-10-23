<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Graph;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class HasCycleTest extends TestCase
{
    public function testHasCycleOnEmptyDirectedGraph(): void
    {
        $graph = Graph\directed();

        static::assertFalse(Graph\has_cycle($graph));
    }

    public function testHasCycleOnSingleNodeDirectedGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertFalse(Graph\has_cycle($graph));
    }

    public function testHasCycleOnDirectedGraphWithoutCycle(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'A', 'C');

        static::assertFalse(Graph\has_cycle($graph));
    }

    public function testHasCycleOnDirectedGraphWithSimpleCycle(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'C', 'A');

        static::assertTrue(Graph\has_cycle($graph));
    }

    public function testHasCycleOnDirectedGraphWithSelfLoop(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'A');

        static::assertTrue(Graph\has_cycle($graph));
    }

    public function testHasCycleOnUndirectedGraphWithoutCycle(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');

        static::assertFalse(Graph\has_cycle($graph));
    }

    public function testHasCycleOnUndirectedGraphWithCycle(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'C', 'A');

        static::assertTrue(Graph\has_cycle($graph));
    }

    public function testHasCycleOnDisconnectedDirectedGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'C', 'D');
        $graph = Graph\add_edge($graph, 'D', 'C');

        static::assertTrue(Graph\has_cycle($graph));
    }

    public function testHasCycleOnComplexDirectedGraphWithoutCycle(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'A', 'C');
        $graph = Graph\add_edge($graph, 'B', 'D');
        $graph = Graph\add_edge($graph, 'C', 'D');
        $graph = Graph\add_edge($graph, 'D', 'E');

        static::assertFalse(Graph\has_cycle($graph));
    }

    public function testHasCycleOnComplexDirectedGraphWithCycle(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'C', 'D');
        $graph = Graph\add_edge($graph, 'D', 'B');

        static::assertTrue(Graph\has_cycle($graph));
    }
}
