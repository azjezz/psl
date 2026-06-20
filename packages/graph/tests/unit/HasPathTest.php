<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class HasPathTest extends TestCase
{
    public function testHasPathBetweenConnectedNodes(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');

        static::assertTrue(Graph\has_path::<string, int>($graph, 'A', 'C'));
    }

    public function testHasPathBetweenDisconnectedNodes(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_node::<string, int>($graph, 'C');

        static::assertFalse(Graph\has_path::<string, int>($graph, 'A', 'C'));
    }

    public function testHasPathFromNodeToItself(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertTrue(Graph\has_path::<string, int>($graph, 'A', 'A'));
    }

    public function testHasPathWhenFromNodeDoesNotExist(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertFalse(Graph\has_path::<string, int>($graph, 'B', 'A'));
    }

    public function testHasPathWhenToNodeDoesNotExist(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertFalse(Graph\has_path::<string, int>($graph, 'A', 'B'));
    }

    public function testHasPathInDirectedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');

        static::assertTrue(Graph\has_path::<string, int>($graph, 'A', 'B'));
        static::assertFalse(Graph\has_path::<string, int>($graph, 'B', 'A'));
    }

    public function testHasPathInUndirectedGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');

        static::assertTrue(Graph\has_path::<string, int>($graph, 'A', 'B'));
        static::assertTrue(Graph\has_path::<string, int>($graph, 'B', 'A'));
    }
}
