<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Graph;

use PHPUnit\Framework\TestCase;
use Psl\Graph;

final class TopologicalSortTest extends TestCase
{
    public function testTopologicalSortOnEmptyGraph(): void
    {
        $graph = Graph\directed();

        static::assertSame([], Graph\topological_sort($graph));
    }

    public function testTopologicalSortOnSingleNode(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_node($graph, 'A');

        static::assertSame(['A'], Graph\topological_sort($graph));
    }

    public function testTopologicalSortOnDAG(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'A', 'C');
        $graph = Graph\add_edge($graph, 'B', 'D');
        $graph = Graph\add_edge($graph, 'C', 'D');

        $result = Graph\topological_sort($graph);
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
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'C', 'A');

        static::assertNull(Graph\topological_sort($graph));
    }

    public function testTopologicalSortOnLinearChain(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'B', 'C');
        $graph = Graph\add_edge($graph, 'C', 'D');

        static::assertSame(['A', 'B', 'C', 'D'], Graph\topological_sort($graph));
    }

    public function testTopologicalSortWithDisconnectedComponents(): void
    {
        $graph = Graph\directed();
        $graph = Graph\add_edge($graph, 'A', 'B');
        $graph = Graph\add_edge($graph, 'C', 'D');

        $result = Graph\topological_sort($graph);
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
