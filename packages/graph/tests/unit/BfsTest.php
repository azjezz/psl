<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

use function array_search;

final class BfsTest extends TestCase
{
    public function testBfsOnSingleNode(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame(['A'], Graph\bfs::<string, int>($graph, 'A'));
    }

    public function testBfsOnLinearGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        static::assertSame(['A', 'B', 'C', 'D'], Graph\bfs::<string, int>($graph, 'A'));
    }

    public function testBfsOnTreeGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'E');

        $result = Graph\bfs::<string, int>($graph, 'A');
        static::assertSame('A', $result[0]);
        static::assertContains('B', $result);
        static::assertContains('C', $result);
        static::assertContains('D', $result);
        static::assertContains('E', $result);
        // B and C should come before D and E
        $bIndex = array_search('B', $result, true);
        $cIndex = array_search('C', $result, true);
        $dIndex = array_search('D', $result, true);
        $eIndex = array_search('E', $result, true);
        static::assertTrue($bIndex < $dIndex);
        static::assertTrue($bIndex < $eIndex);
    }

    public function testBfsFromNonExistentNode(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame([], Graph\bfs::<string, int>($graph, 'B'));
    }

    public function testBfsOnDisconnectedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_node::<string, int>($graph, 'C');

        $result = Graph\bfs::<string, int>($graph, 'A');
        static::assertContains('A', $result);
        static::assertContains('B', $result);
        static::assertNotContains('C', $result);
    }
}
