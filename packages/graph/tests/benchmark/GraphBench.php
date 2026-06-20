<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Graph;

#[Groups(['graph'])]
final class GraphBench
{
    /**
     * @param array{graph: Graph\DirectedGraph, start: string} $params
     */
    #[ParamProviders('provideDfsData')]
    public function benchDfs(array $params): void
    {
        $_ = Graph\dfs::<string, int>($params['graph'], $params['start']);
    }

    /**
     * @param array{graph: Graph\DirectedGraph<string, int>, from: string, to: string} $params
     */
    #[ParamProviders('provideShortestPathData')]
    public function benchShortestPath(array $params): void
    {
        $_ = Graph\shortest_path::<string>($params['graph'], $params['from'], $params['to']);
    }

    /**
     * @param array{graph: Graph\DirectedGraph, node: string} $params
     */
    #[ParamProviders('provideNeighborsData')]
    public function benchNeighbors(array $params): void
    {
        $_ = Graph\neighbors::<string, int>($params['graph'], $params['node']);
    }

    /**
     * @param array{graph: Graph\DirectedGraph} $params
     */
    #[ParamProviders('provideTopoSortData')]
    public function benchTopologicalSort(array $params): void
    {
        $_ = Graph\topological_sort::<string, int>($params['graph']);
    }

    /**
     * @return iterable<string, array{graph: Graph\DirectedGraph, start: string}>
     */
    public function provideDfsData(): iterable
    {
        $graph = Graph\directed::<string, int>();
        for ($i = 0; $i < 49; $i++) {
            $graph = Graph\add_edge::<string, int>($graph, 'n' . $i, 'n' . ($i + 1));
        }

        yield 'chain_50' => ['graph' => $graph, 'start' => 'n0'];

        $graph = Graph\directed::<string, int>();
        for ($i = 0; $i < 50; $i++) {
            $graph = Graph\add_edge::<string, int>($graph, 'center', 'leaf' . $i);
        }

        yield 'star_50' => ['graph' => $graph, 'start' => 'center'];

        $graph = Graph\directed::<string, int>();
        for ($r = 0; $r < 10; $r++) {
            for ($c = 0; $c < 9; $c++) {
                $graph = Graph\add_edge::<string, int>($graph, $r . '_' . $c, $r . '_' . ($c + 1));
            }

            if ($r < 9) {
                for ($c = 0; $c < 10; $c++) {
                    $graph = Graph\add_edge::<string, int>($graph, $r . '_' . $c, ($r + 1) . '_' . $c);
                }
            }
        }

        yield 'grid_10x10' => ['graph' => $graph, 'start' => '0_0'];
    }

    /**
     * @return iterable<string, array{graph: Graph\DirectedGraph<string, int>, from: string, to: string}>
     */
    public function provideShortestPathData(): iterable
    {
        /** @var Graph\DirectedGraph<string, int> $graph */
        $graph = Graph\directed::<string, int>();
        for ($i = 0; $i < 50; $i++) {
            $graph = Graph\add_edge::<string, int>($graph, 'n' . $i, 'n' . ($i + 1), 1);
        }

        yield 'chain_50' => ['graph' => $graph, 'from' => 'n0', 'to' => 'n50'];

        /** @var Graph\DirectedGraph<string, int> $graph */
        $graph = Graph\directed::<string, int>();
        for ($r = 0; $r < 10; $r++) {
            for ($c = 0; $c < 9; $c++) {
                $graph = Graph\add_edge::<string, int>($graph, $r . '_' . $c, $r . '_' . ($c + 1), 1);
            }

            if ($r < 9) {
                for ($c = 0; $c < 10; $c++) {
                    $graph = Graph\add_edge::<string, int>($graph, $r . '_' . $c, ($r + 1) . '_' . $c, 1);
                }
            }
        }

        yield 'grid_10x10' => ['graph' => $graph, 'from' => '0_0', 'to' => '9_9'];
    }

    /**
     * @return iterable<string, array{graph: Graph\DirectedGraph, node: string}>
     */
    public function provideNeighborsData(): iterable
    {
        $graph = Graph\directed::<string, int>();
        for ($i = 0; $i < 100; $i++) {
            $graph = Graph\add_edge::<string, int>($graph, 'center', 'leaf' . $i);
        }

        yield 'star_100' => ['graph' => $graph, 'node' => 'center'];
    }

    /**
     * @return iterable<string, array{graph: Graph\DirectedGraph}>
     */
    public function provideTopoSortData(): iterable
    {
        $graph = Graph\directed::<string, int>();
        for ($layer = 0; $layer < 5; $layer++) {
            for ($n = 0; $n < 10; $n++) {
                if ($layer >= 4) {
                    continue;
                }

                for ($next = 0; $next < 10; $next++) {
                    $graph = Graph\add_edge::<string, int>($graph, $layer . '_' . $n, ($layer + 1) . '_' . $next);
                }
            }
        }

        yield 'layered_5x10' => ['graph' => $graph];
    }
}
