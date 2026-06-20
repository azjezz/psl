<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class LeavesTest extends TestCase
{
    public function testLeavesSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        $result = Tree\leaves::<int>($tree);

        static::assertSame([42], $result);
    }

    public function testLeavesMultipleLeaves(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\leaf::<int>(3),
            Tree\leaf::<int>(4),
        ]);

        $result = Tree\leaves::<int>($tree);

        static::assertSame([2, 3, 4], $result);
    }

    public function testLeavesNestedTree(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [Tree\leaf::<int>(3), Tree\leaf::<int>(4)]),
            Tree\leaf::<int>(5),
            Tree\tree::<int>(6, [Tree\leaf::<int>(7)]),
        ]);

        $result = Tree\leaves::<int>($tree);

        static::assertSame([3, 4, 5, 7], $result);
    }

    public function testLeavesTreeNodeWithNoChildren(): void
    {
        $tree = Tree\tree::<string>('alone', []);

        $result = Tree\leaves::<string>($tree);

        static::assertSame(['alone'], $result);
    }

    public function testLeavesDeepTree(): void
    {
        $tree = Tree\tree::<string>('root', [
            Tree\tree::<string>('branch1', [
                Tree\tree::<string>('branch2', [
                    Tree\leaf::<string>('leaf1'),
                ]),
            ]),
            Tree\leaf::<string>('leaf2'),
        ]);

        $result = Tree\leaves::<string>($tree);

        static::assertSame(['leaf1', 'leaf2'], $result);
    }
}
