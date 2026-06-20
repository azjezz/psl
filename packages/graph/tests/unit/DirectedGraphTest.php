<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class DirectedGraphTest extends TestCase
{
    public function testEmptyDirectedGraph(): void
    {
        $graph = new Graph\DirectedGraph::<string, int>([], []);

        static::assertSame([], $graph->getNodes());
        static::assertSame([], $graph->getEdgesFrom('any-node'));
    }

    public function testGetNodes(): void
    {
        $graph = new Graph\DirectedGraph::<string, int>(['s:A' => 'A', 's:B' => 'B', 's:C' => 'C'], [
            's:A' => [],
            's:B' => [],
            's:C' => [],
        ]);

        static::assertSame(['A', 'B', 'C'], $graph->getNodes());
    }

    public function testGetEdgesFrom(): void
    {
        $edges = [
            new Graph\Edge::<string, int>('B'),
            new Graph\Edge::<string, int>('C', 5),
        ];
        $graph = new Graph\DirectedGraph::<string, int>(['s:A' => 'A'], ['s:A' => $edges]);

        static::assertSame($edges, $graph->getEdgesFrom('A'));
    }

    public function testGetEdgesFromNonExistentNode(): void
    {
        $graph = new Graph\DirectedGraph::<string, int>(['s:A' => 'A'], ['s:A' => []]);

        static::assertSame([], $graph->getEdgesFrom('B'));
    }

    public function testHasNode(): void
    {
        $graph = new Graph\DirectedGraph::<string, int>(['s:A' => 'A', 's:B' => 'B'], ['s:A' => [], 's:B' => []]);

        static::assertTrue($graph->hasNode('A'));
        static::assertTrue($graph->hasNode('B'));
        static::assertFalse($graph->hasNode('C'));
    }

    public function testHasEdge(): void
    {
        $graph = new Graph\DirectedGraph::<string, int>(['s:A' => 'A', 's:B' => 'B', 's:C' => 'C'], [
            's:A' => [new Graph\Edge::<string, int>('B'), new Graph\Edge::<string, int>('C')],
            's:B' => [],
            's:C' => [],
        ]);

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertTrue($graph->hasEdge('A', 'C'));
        static::assertFalse($graph->hasEdge('B', 'A'));
        static::assertFalse($graph->hasEdge('B', 'C'));
    }

    public function testHasEdgeFromNonExistentNode(): void
    {
        $graph = new Graph\DirectedGraph::<string, int>(['s:A' => 'A'], ['s:A' => []]);

        static::assertFalse($graph->hasEdge('B', 'A'));
    }

    public function testHasCycleOnEmptyGraph(): void
    {
        $graph = new Graph\DirectedGraph::<string, int>([], []);

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnGraphWithoutCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnGraphWithSimpleCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnGraphWithSelfLoop(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'A');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnDisconnectedGraphWithCycleInFirstComponent(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'A');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnDisconnectedGraphWithCycleInSecondComponent(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'C');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnComplexGraphWithMultiplePathsNoCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'E');

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnGraphWithNestedCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'E');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleDetectsDirectCycleCorrectly(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertTrue($graph->hasCycle());

        $acyclic = Graph\directed::<string, int>();
        $acyclic = Graph\add_edge::<string, int>($acyclic, 'X', 'Y');
        $acyclic = Graph\add_edge::<string, int>($acyclic, 'Y', 'Z');
        static::assertFalse($acyclic->hasCycle());
    }

    public function testHasCycleWithNodeHavingMultipleEdgesFirstNeighborVisited(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleWithMultipleEdgesContinueVsBreak(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertTrue($graph->hasCycle());
    }
}
