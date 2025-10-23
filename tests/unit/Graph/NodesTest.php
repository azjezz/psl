<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Graph;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class NodesTest extends TestCase
{
    public function testNodesOnEmptyGraph(): void
    {
        $graph = Graph\directed();

        static::assertSame([], Graph\nodes($graph));
    }

    public function testNodesOnGraphWithNodes(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');
        $graph = Graph\add_node($graph, 'B');
        $graph = Graph\add_node($graph, 'C');

        static::assertSame(['A', 'B', 'C'], Graph\nodes($graph));
    }

    public function testNodesOnUndirectedGraph(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_edge($graph, 'A', 'B');

        $nodes = Graph\nodes($graph);
        static::assertCount(2, $nodes);
        static::assertContains('A', $nodes);
        static::assertContains('B', $nodes);
    }
}
