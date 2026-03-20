<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Tree;

use function array_fill;

#[Groups(['tree'])]
final class TreeBench
{
    /**
     * @param array{tree: Tree\NodeInterface} $params
     */
    #[ParamProviders('provideTreeData')]
    public function benchDepth(array $params): void
    {
        Tree\depth($params['tree']);
    }

    /**
     * @param array{tree: Tree\NodeInterface, path: list<non-negative-int>} $params
     */
    #[ParamProviders('provideAtIndexData')]
    public function benchAtIndex(array $params): void
    {
        Tree\at_index($params['tree'], $params['path']);
    }

    /**
     * @return iterable<string, array{tree: Tree\NodeInterface}>
     */
    public function provideTreeData(): iterable
    {
        $children = [];
        for ($i = 0; $i < 10; $i++) {
            $grandchildren = [];
            for ($j = 0; $j < 10; $j++) {
                $grandchildren[] = Tree\leaf(($i * 10) + $j);
            }

            $children[] = Tree\tree($i, $grandchildren);
        }

        yield 'wide_shallow' => ['tree' => Tree\tree(0, $children)];

        $node = Tree\leaf(0);
        for ($i = 0; $i < 20; $i++) {
            $node = Tree\tree($i, [$node]);
        }

        yield 'deep_narrow' => ['tree' => $node];

        // Balanced binary tree: depth 8
        yield 'balanced_depth8' => ['tree' => self::buildBalancedTree(8)];
    }

    /**
     * @return iterable<string, array{tree: Tree\NodeInterface, path: list<non-negative-int>}>
     */
    public function provideAtIndexData(): iterable
    {
        $node = Tree\leaf(0);
        for ($i = 0; $i < 15; $i++) {
            $node = Tree\tree($i, [$node]);
        }

        yield 'deep_path' => ['tree' => $node, 'path' => array_fill(0, 15, 0)];

        $children = [];
        for ($i = 0; $i < 20; $i++) {
            $grandchildren = [];
            for ($j = 0; $j < 5; $j++) {
                $grandchildren[] = Tree\leaf(($i * 5) + $j);
            }

            $children[] = Tree\tree($i, $grandchildren);
        }

        yield 'wide_middle' => ['tree' => Tree\tree(0, $children), 'path' => [10, 2]];
    }

    private static function buildBalancedTree(int $depth): Tree\NodeInterface
    {
        if ($depth === 0) {
            return Tree\leaf(0);
        }

        return Tree\tree(0, [
            self::buildBalancedTree($depth - 1),
            self::buildBalancedTree($depth - 1),
        ]);
    }
}
