<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Graph;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Graph;
use stdClass;

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

    #[DataProvider('provideNodeTypes')]
    public function testAddNodeWithVariousTypes(mixed $node): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, $node);

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
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 1, 2);
        $graph = Graph\add_edge($graph, 2, 3);

        static::assertSame([1, 2, 3], Graph\dfs($graph, 1));
    }
}
