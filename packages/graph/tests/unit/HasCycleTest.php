<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class HasCycleTest extends TestCase
{
    public function testHasCycleOnEmptyDirectedGraph(): void
    {
        $graph = Graph\directed::<string, int>();

        static::assertFalse(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnSingleNodeDirectedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertFalse(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnDirectedGraphWithoutCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');

        static::assertFalse(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnDirectedGraphWithSimpleCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertTrue(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnDirectedGraphWithSelfLoop(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'A');

        static::assertTrue(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnUndirectedGraphWithoutCycle(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');

        static::assertFalse(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnUndirectedGraphWithCycle(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertTrue(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnDisconnectedDirectedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'C');

        static::assertTrue(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnComplexDirectedGraphWithoutCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'E');

        static::assertFalse(Graph\has_cycle::<string, int>($graph));
    }

    public function testHasCycleOnComplexDirectedGraphWithCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'B');

        static::assertTrue(Graph\has_cycle::<string, int>($graph));
    }
}
