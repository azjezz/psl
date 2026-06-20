<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class LevelOrderTest extends TestCase
{
    public function testLevelOrderTraversal(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\tree::<int>(3, [Tree\leaf::<int>(4), Tree\leaf::<int>(5)]),
            Tree\leaf::<int>(6),
        ]);

        $result = Tree\level_order::<int>($tree);

        static::assertSame([1, 2, 3, 6, 4, 5], $result);
    }

    public function testLevelOrderSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        $result = Tree\level_order::<int>($tree);

        static::assertSame([42], $result);
    }

    public function testLevelOrderDeepTree(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\tree::<string>('b', [
                Tree\tree::<string>('c', [
                    Tree\leaf::<string>('d'),
                ]),
            ]),
            Tree\leaf::<string>('e'),
        ]);

        $result = Tree\level_order::<string>($tree);

        static::assertSame(['a', 'b', 'e', 'c', 'd'], $result);
    }
}
