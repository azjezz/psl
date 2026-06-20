<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class ShortestPathByTest extends TestCase
{
    public function testShortestPathByWithFloatWeights(): void
    {
        $graph = Graph\directed::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B', 1.5);
        $graph = Graph\add_edge::<string, float>($graph, 'B', 'C', 2.3);
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'C', 5.1);

        // Convert float to int by multiplying by 10
        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'C', static fn(float $w): int => (int) ($w * 10));

        static::assertSame(['A', 'B', 'C'], $path);
    }

    public function testShortestPathByWithFloatWeightsHighPrecision(): void
    {
        $graph = Graph\directed::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B', 1.001);
        $graph = Graph\add_edge::<string, float>($graph, 'B', 'C', 1.002);
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'C', 2.5);

        // Convert float to int by multiplying by 1000 for higher precision
        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'C', static fn(float $w): int => (int) ($w * 1000));

        static::assertSame(['A', 'B', 'C'], $path);
    }

    public function testShortestPathByWithComplexGraph(): void
    {
        $graph = Graph\directed::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B', 4.5);
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'C', 2.0);
        $graph = Graph\add_edge::<string, float>($graph, 'B', 'C', 1.5);
        $graph = Graph\add_edge::<string, float>($graph, 'B', 'D', 3.0);
        $graph = Graph\add_edge::<string, float>($graph, 'C', 'D', 5.5);

        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'D', static fn(float $w): int => (int) ($w * 10));

        // Both A->B->D (7.5) and A->C->D (7.5) have the same cost, either is valid
        static::assertContains($path, [['A', 'B', 'D'], ['A', 'C', 'D']]);
    }

    public function testShortestPathByNoPath(): void
    {
        $graph = Graph\directed::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B', 1.5);
        $graph = Graph\add_edge::<string, float>($graph, 'C', 'D', 2.0);

        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'D', static fn(float $w): int => (int) ($w * 10));

        static::assertNull($path);
    }

    public function testShortestPathByNonExistentNodes(): void
    {
        $graph = Graph\directed::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B', 1.5);

        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'Z', static fn(float $w): int => (int) ($w * 10));

        static::assertNull($path);
    }

    public function testShortestPathBySameNode(): void
    {
        $graph = Graph\directed::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B', 1.5);

        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'A', static fn(float $w): int => (int) ($w * 10));

        static::assertSame(['A'], $path);
    }

    public function testShortestPathByUnweightedGraph(): void
    {
        // Unweighted graph (all weights are null) should use BFS
        $graph = Graph\directed::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, float>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'C');

        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'C', static fn(null|float $w): int => $w === null
            ? 1
            : (int) ($w * 10));

        static::assertSame(['A', 'C'], $path);
    }

    public function testShortestPathByUndirectedGraph(): void
    {
        $graph = Graph\undirected::<string, float>();
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'B', 1.5);
        $graph = Graph\add_edge::<string, float>($graph, 'B', 'C', 2.0);
        $graph = Graph\add_edge::<string, float>($graph, 'A', 'C', 5.0);

        $path = Graph\shortest_path_by::<string, float>($graph, 'A', 'C', static fn(float $w): int => (int) ($w * 10));

        static::assertSame(['A', 'B', 'C'], $path);
    }

    public function testShortestPathByWithStringWeights(): void
    {
        // Demonstrate that any weight type can be used
        $graph = Graph\directed::<string, string>();
        $graph = Graph\add_edge::<string, string>($graph, 'A', 'B', 'low');
        $graph = Graph\add_edge::<string, string>($graph, 'B', 'C', 'medium');
        $graph = Graph\add_edge::<string, string>($graph, 'A', 'C', 'high');

        $weightMap = ['low' => 1, 'medium' => 2, 'high' => 5];
        $path = Graph\shortest_path_by::<string, string>($graph, 'A', 'C', static fn(string $w): int => $weightMap[$w]);

        static::assertSame(['A', 'B', 'C'], $path);
    }
}
