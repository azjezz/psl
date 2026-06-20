<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class IsLeafTest extends TestCase
{
    public function testIsLeafReturnsTrueForLeafNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        static::assertTrue(Tree\is_leaf::<int>($tree));
    }

    public function testIsLeafReturnsFalseForTreeNode(): void
    {
        $tree = Tree\tree::<int>(1, [Tree\leaf::<int>(2)]);

        static::assertFalse(Tree\is_leaf::<int>($tree));
    }

    public function testIsLeafReturnsFalseForTreeNodeWithoutChildren(): void
    {
        $tree = Tree\tree::<int>(1, []);

        static::assertFalse(Tree\is_leaf::<int>($tree));
    }
}
