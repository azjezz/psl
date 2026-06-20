<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class NodesTest extends TestCase
{
    public function testNodesOnEmptyGraph(): void
    {
        $graph = Graph\directed::<string, int>();

        static::assertSame([], Graph\nodes::<string, int>($graph));
    }

    public function testNodesOnGraphWithNodes(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');
        $graph = Graph\add_node::<string, int>($graph, 'B');
        $graph = Graph\add_node::<string, int>($graph, 'C');

        static::assertSame(['A', 'B', 'C'], Graph\nodes::<string, int>($graph));
    }

    public function testNodesOnUndirectedGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');

        $nodes = Graph\nodes::<string, int>($graph);
        static::assertCount(2, $nodes);
        static::assertContains('A', $nodes);
        static::assertContains('B', $nodes);
    }
}
