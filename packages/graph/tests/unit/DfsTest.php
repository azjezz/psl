<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class DfsTest extends TestCase
{
    public function testDfsOnSingleNode(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertSame(['A'], Graph\dfs($graph, 'A'));
    }

    public function testDfsOnLinearGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'C', 'D');

        static::assertSame(['A', 'B', 'C', 'D'], Graph\dfs($graph, 'A'));
    }

    public function testDfsOnTreeGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'A', 'C');
        $graph = Graph\add_edge($graph, 'B', 'D');

        $result = Graph\dfs($graph, 'A');
        static::assertSame('A', $result[0]);
        static::assertContains('B', $result);
        static::assertContains('C', $result);
        static::assertContains('D', $result);
    }

    public function testDfsFromNonExistentNode(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertSame([], Graph\dfs($graph, 'B'));
    }

    public function testDfsOnGraphWithCycle(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'C', 'A');

        $result = Graph\dfs($graph, 'A');
        static::assertCount(3, $result);
        static::assertContains('A', $result);
        static::assertContains('B', $result);
        static::assertContains('C', $result);
    }

    public function testDfsSkipsAlreadyVisitedNodeFromStack(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'A', 'C');
        $graph = Graph\add_edge($graph, 'B', 'C');

        $result = Graph\dfs($graph, 'A');
        static::assertCount(3, $result);
        static::assertSame('A', $result[0]);
        static::assertContains('B', $result);
        static::assertContains('C', $result);
    }
}
