<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class IsLeafTest extends TestCase
{
    public function testIsLeafReturnsTrueForLeafNode(): void
    {
        $tree = Tree\leaf(42);

        static::assertTrue(Tree\is_leaf($tree));
    }

    public function testIsLeafReturnsFalseForTreeNode(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2)]);

        static::assertFalse(Tree\is_leaf($tree));
    }

    public function testIsLeafReturnsFalseForTreeNodeWithoutChildren(): void
    {
        $tree = Tree\tree(1, []);

        static::assertFalse(Tree\is_leaf($tree));
    }
}
