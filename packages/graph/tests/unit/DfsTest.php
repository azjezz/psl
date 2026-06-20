<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class DfsTest extends TestCase
{
    public function testDfsOnSingleNode(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame(['A'], Graph\dfs::<string, int>($graph, 'A'));
    }

    public function testDfsOnLinearGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        static::assertSame(['A', 'B', 'C', 'D'], Graph\dfs::<string, int>($graph, 'A'));
    }

    public function testDfsOnTreeGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');

        $result = Graph\dfs::<string, int>($graph, 'A');
        static::assertSame('A', $result[0]);
        static::assertContains('B', $result);
        static::assertContains('C', $result);
        static::assertContains('D', $result);
    }

    public function testDfsFromNonExistentNode(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame([], Graph\dfs::<string, int>($graph, 'B'));
    }

    public function testDfsOnGraphWithCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        $result = Graph\dfs::<string, int>($graph, 'A');
        static::assertCount(3, $result);
        static::assertContains('A', $result);
        static::assertContains('B', $result);
        static::assertContains('C', $result);
    }

    public function testDfsSkipsAlreadyVisitedNodeFromStack(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');

        $result = Graph\dfs::<string, int>($graph, 'A');
        static::assertCount(3, $result);
        static::assertSame('A', $result[0]);
        static::assertContains('B', $result);
        static::assertContains('C', $result);
    }
}
