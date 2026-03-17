<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class LevelOrderTest extends TestCase
{
    public function testLevelOrderTraversal(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\tree(3, [Tree\leaf(4), Tree\leaf(5)]),
            Tree\leaf(6),
        ]);

        $result = Tree\level_order($tree);

        static::assertSame([1, 2, 3, 6, 4, 5], $result);
    }

    public function testLevelOrderSingleNode(): void
    {
        $tree = Tree\leaf(42);

        $result = Tree\level_order($tree);

        static::assertSame([42], $result);
    }

    public function testLevelOrderDeepTree(): void
    {
        $tree = Tree\tree('a', [
            Tree\tree('b', [
                Tree\tree('c', [
                    Tree\leaf('d'),
                ]),
            ]),
            Tree\leaf('e'),
        ]);

        $result = Tree\level_order($tree);

        static::assertSame(['a', 'b', 'e', 'c', 'd'], $result);
    }
}
