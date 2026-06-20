<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

use function array_search;

final class TopologicalSortTest extends TestCase
{
    public function testTopologicalSortOnEmptyGraph(): void
    {
        $graph = Graph\directed::<string, int>();

        static::assertSame([], Graph\topological_sort::<string, int>($graph));
    }

    public function testTopologicalSortOnSingleNode(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_node::<string, int>($graph, 'A');

        static::assertSame(['A'], Graph\topological_sort::<string, int>($graph));
    }

    public function testTopologicalSortOnDAG(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'D');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        $result = Graph\topological_sort::<string, int>($graph);
        static::assertNotNull($result);
        static::assertCount(4, $result);

        // Verify topological order
        $aIndex = array_search('A', $result, true);
        $bIndex = array_search('B', $result, true);
        $cIndex = array_search('C', $result, true);
        $dIndex = array_search('D', $result, true);

        static::assertTrue($aIndex < $bIndex);
        static::assertTrue($aIndex < $cIndex);
        static::assertTrue($bIndex < $dIndex);
        static::assertTrue($cIndex < $dIndex);
    }

    public function testTopologicalSortOnGraphWithCycle(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'A');

        static::assertNull(Graph\topological_sort::<string, int>($graph));
    }

    public function testTopologicalSortOnLinearChain(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'B', 'C');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        static::assertSame(['A', 'B', 'C', 'D'], Graph\topological_sort::<string, int>($graph));
    }

    public function testTopologicalSortWithDisconnectedComponents(): void
    {
        $graph = Graph\directed::<string, int>();
        $graph = Graph\add_edge::<string, int>($graph, 'A', 'B');
        $graph = Graph\add_edge::<string, int>($graph, 'C', 'D');

        $result = Graph\topological_sort::<string, int>($graph);
        static::assertNotNull($result);
        static::assertCount(4, $result);

        $aIndex = array_search('A', $result, true);
        $bIndex = array_search('B', $result, true);
        $cIndex = array_search('C', $result, true);
        $dIndex = array_search('D', $result, true);

        static::assertTrue($aIndex < $bIndex);
        static::assertTrue($cIndex < $dIndex);
    }
}
