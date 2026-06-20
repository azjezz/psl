<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class UndirectedGraphTest extends TestCase
{
    public function testEmptyUndirectedGraph(): void
    {
        $graph = new Graph\UndirectedGraph::<string, int>([], []);

        static::assertSame([], $graph->getNodes());
        static::assertSame([], $graph->getEdgesFrom('any-node'));
    }

    public function testGetNodes(): void
    {
        $graph = new Graph\UndirectedGraph::<string, int>(['s:A' => 'A', 's:B' => 'B', 's:C' => 'C'], [
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
        $graph = new Graph\UndirectedGraph::<string, int>(['s:A' => 'A'], ['s:A' => $edges]);

        static::assertSame($edges, $graph->getEdgesFrom('A'));
    }

    public function testGetEdgesFromNonExistentNode(): void
    {
        $graph = new Graph\UndirectedGraph::<string, int>(['s:A' => 'A'], ['s:A' => []]);

        static::assertSame([], $graph->getEdgesFrom('B'));
    }

    public function testHasNode(): void
    {
        $graph = new Graph\UndirectedGraph::<string, int>(['s:A' => 'A', 's:B' => 'B'], ['s:A' => [], 's:B' => []]);

        static::assertTrue($graph->hasNode('A'));
        static::assertTrue($graph->hasNode('B'));
        static::assertFalse($graph->hasNode('C'));
    }

    public function testHasEdge(): void
    {
        $graph = new Graph\UndirectedGraph::<string, int>(['s:A' => 'A', 's:B' => 'B', 's:C' => 'C'], [
            's:A' => [new Graph\Edge::<string, int>('B'), new Graph\Edge::<string, int>('C')],
            's:B' => [new Graph\Edge::<string, int>('A')],
            's:C' => [new Graph\Edge::<string, int>('A')],
        ]);

        static::assertTrue($graph->hasEdge('A', 'B'));
        static::assertTrue($graph->hasEdge('A', 'C'));
        static::assertTrue($graph->hasEdge('B', 'A'));
        static::assertFalse($graph->hasEdge('B', 'C'));
    }

    public function testHasEdgeFromNonExistentNode(): void
    {
        $graph = new Graph\UndirectedGraph::<string, int>(['s:A' => 'A'], ['s:A' => []]);

        static::assertFalse($graph->hasEdge('B', 'A'));
    }

    public function testHasCycleOnEmptyGraph(): void
    {
        $graph = new Graph\UndirectedGraph::<string, int>([], []);

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnSingleNodeGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnGraphWithoutCycle(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnGraphWithSimpleCycle(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnGraphWithSelfLoop(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'A');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnDisconnectedGraphWithCycleInFirstComponent(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'E');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnDisconnectedGraphWithCycleInSecondComponent(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'E');
        $graph = Graph\add_edge::<string, int>($graph, 'E', 'C');

        static::assertTrue($graph->hasCycle());
    }

    public function testHasCycleOnDisconnectedGraphWithoutCycle(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'E', 'F');

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnTreeStructure(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'E');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'F');

        static::assertFalse($graph->hasCycle());
    }

    public function testHasCycleOnComplexGraphWithCycle(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'D', 'E');
        $graph = Graph\add_edge::<string, int>($graph, 'E', 'B');

        static::assertTrue($graph->hasCycle());
    }

    public function testAddDuplicateNodeReturnsSameGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');
        $graph2 = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame($graph, $graph2);
    }

    public function testHasCycleDetectsTriangleCycleRequiringVisitedFlag(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertTrue($graph->hasCycle());

        $linear = Graph\undirected::<string, int>();
        $linear = Graph\add_edge::<string, int>($linear, 'X', 'Y');
        $linear = Graph\add_edge::<string, int>($linear, 'Y', 'Z');

        static::assertFalse($linear->hasCycle());
    }
}
