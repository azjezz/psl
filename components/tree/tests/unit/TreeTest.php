<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class TreeTest extends TestCase
{
    public function testTreeCreatesTreeNode(): void
    {
        $tree = Tree\tree('root');

        static::assertInstanceOf(Tree\TreeNode::class, $tree);
        static::assertSame('root', $tree->getValue());
        static::assertSame([], $tree->getChildren());
    }

    public function testTreeWithChildren(): void
    {
        $tree = Tree\tree('root', [
            Tree\leaf('child1'),
            Tree\leaf('child2'),
        ]);

        static::assertSame('root', $tree->getValue());
        static::assertCount(2, $tree->getChildren());
    }

    public function testTreeNodeJsonSerialize(): void
    {
        $tree = Tree\tree('root', [
            Tree\leaf('child1'),
        ]);

        $array = $tree->jsonSerialize();

        static::assertSame('root', $array['value']);
        static::assertArrayHasKey('children', $array);
    }
}
