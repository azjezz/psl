<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Graph;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class AddNodeTest extends TestCase
{
    public function testAddNodeToDirectedGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertTrue($graph->hasNode('A'));
        static::assertSame(['A'], $graph->getNodes());
    }

    public function testAddNodeToUndirectedGraph(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_node($graph, 'A');

        static::assertTrue($graph->hasNode('A'));
        static::assertSame(['A'], $graph->getNodes());
    }

    public function testAddMultipleNodes(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');
        $graph = Graph\add_node($graph, 'B');
        $graph = Graph\add_node($graph, 'C');

        static::assertCount(3, $graph->getNodes());
        static::assertTrue($graph->hasNode('A'));
        static::assertTrue($graph->hasNode('B'));
        static::assertTrue($graph->hasNode('C'));
    }

    public function testAddDuplicateNodeReturnsSameGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');
        $graph2 = Graph\add_node($graph, 'A');

        static::assertSame($graph, $graph2);
    }
}
