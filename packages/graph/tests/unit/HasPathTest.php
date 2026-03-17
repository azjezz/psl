<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class HasPathTest extends TestCase
{
    public function testHasPathBetweenConnectedNodes(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');

        static::assertTrue(Graph\has_path($graph, 'A', 'C'));
    }

    public function testHasPathBetweenDisconnectedNodes(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_node($graph, 'C');

        static::assertFalse(Graph\has_path($graph, 'A', 'C'));
    }

    public function testHasPathFromNodeToItself(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertTrue(Graph\has_path($graph, 'A', 'A'));
    }

    public function testHasPathWhenFromNodeDoesNotExist(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertFalse(Graph\has_path($graph, 'B', 'A'));
    }

    public function testHasPathWhenToNodeDoesNotExist(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertFalse(Graph\has_path($graph, 'A', 'B'));
    }

    public function testHasPathInDirectedGraph(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');

        static::assertTrue(Graph\has_path($graph, 'A', 'B'));
        static::assertFalse(Graph\has_path($graph, 'B', 'A'));
    }

    public function testHasPathInUndirectedGraph(): void
    {
        $graph = Graph\undirected();
        $graph = Graph\add_edge($graph, 'A', 'B');

        static::assertTrue(Graph\has_path($graph, 'A', 'B'));
        static::assertTrue(Graph\has_path($graph, 'B', 'A'));
    }
}
