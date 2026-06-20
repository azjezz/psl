<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class ConstructorTest extends TestCase
{
    public function testDirectedConstructor(): void
    {
        $graph = Graph\directed::<string, int>();

        static::assertInstanceOf(Graph\DirectedGraph::class, $graph);
        static::assertSame([], $graph->getNodes());
    }

    public function testUndirectedConstructor(): void
    {
        $graph = Graph\undirected::<string, int>();

        static::assertInstanceOf(Graph\UndirectedGraph::class, $graph);
        static::assertSame([], $graph->getNodes());
    }
}
