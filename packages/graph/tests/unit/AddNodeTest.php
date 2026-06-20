<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Graph;
use stdClass;

final class AddNodeTest extends TestCase
{
    public function testAddNodeToDirectedGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertTrue($graph->hasNode('A'));
        static::assertSame(['A'], $graph->getNodes());
    }

    public function testAddNodeToUndirectedGraph(): void
    {
        $graph = Graph\undirected::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertTrue($graph->hasNode('A'));
        static::assertSame(['A'], $graph->getNodes());
    }

    public function testAddMultipleNodes(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');
        $graph = Graph\add_node::<string, int>($graph, 'B');
        $graph = Graph\add_node::<string, int>($graph, 'C');

        static::assertCount(3, $graph->getNodes());
        static::assertTrue($graph->hasNode('A'));
        static::assertTrue($graph->hasNode('B'));
        static::assertTrue($graph->hasNode('C'));
    }

    public function testAddDuplicateNodeReturnsSameGraph(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');
        $graph2 = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame($graph, $graph2);
    }

    #[DataProvider('provideNodeTypes')]
    public function testAddNodeWithVariousTypes(mixed $node): void
    {
        $graph = Graph\directed::<mixed, mixed>();
        $graph = Graph\add_node::<mixed, mixed>($graph, $node);

        static::assertTrue($graph->hasNode($node));
        static::assertCount(1, $graph->getNodes());
    }

    public static function provideNodeTypes(): iterable
    {
        yield 'int' => [42];
        yield 'float' => [3.14];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'array' => [['a', 'b']];
        yield 'object' => [new stdClass()];
        yield 'resource' => [STDIN];
    }

    public function testAddEdgeWithIntNodes(): void
    {
        $graph = Graph\directed::<int, int>();
        $graph = Graph\add_edge::<int, int>($graph, 1, 2);
        $graph = Graph\add_edge::<int, int>($graph, 2, 3);

        static::assertSame([1, 2, 3], Graph\dfs::<int, int>($graph, 1));
    }
}
