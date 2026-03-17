<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class LeavesTest extends TestCase
{
    public function testLeavesSingleNode(): void
    {
        $tree = Tree\leaf(42);

        $result = Tree\leaves($tree);

        static::assertSame([42], $result);
    }

    public function testLeavesMultipleLeaves(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\leaf(3),
            Tree\leaf(4),
        ]);

        $result = Tree\leaves($tree);

        static::assertSame([2, 3, 4], $result);
    }

    public function testLeavesNestedTree(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [Tree\leaf(3), Tree\leaf(4)]),
            Tree\leaf(5),
            Tree\tree(6, [Tree\leaf(7)]),
        ]);

        $result = Tree\leaves($tree);

        static::assertSame([3, 4, 5, 7], $result);
    }

    public function testLeavesTreeNodeWithNoChildren(): void
    {
        $tree = Tree\tree('alone', []);

        $result = Tree\leaves($tree);

        static::assertSame(['alone'], $result);
    }

    public function testLeavesDeepTree(): void
    {
        $tree = Tree\tree('root', [
            Tree\tree('branch1', [
                Tree\tree('branch2', [
                    Tree\leaf('leaf1'),
                ]),
            ]),
            Tree\leaf('leaf2'),
        ]);

        $result = Tree\leaves($tree);

        static::assertSame(['leaf1', 'leaf2'], $result);
    }
}
