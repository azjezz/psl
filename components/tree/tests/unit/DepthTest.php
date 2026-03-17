<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class DepthTest extends TestCase
{
    public function testDepthSingleNode(): void
    {
        $tree = Tree\leaf(1);

        $result = Tree\depth($tree);

        static::assertSame(0, $result);
    }

    public function testDepthOneLevel(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\leaf(3),
        ]);

        $result = Tree\depth($tree);

        static::assertSame(1, $result);
    }

    public function testDepthMultipleLevels(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [Tree\leaf(3)]),
            Tree\leaf(4),
        ]);

        $result = Tree\depth($tree);

        static::assertSame(2, $result);
    }

    public function testDepthTreeNodeWithNoChildren(): void
    {
        $tree = Tree\tree(1, []);

        $result = Tree\depth($tree);

        static::assertSame(0, $result);
    }

    public function testDepthDeepTree(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [
                Tree\tree(3, [
                    Tree\tree(4, [
                        Tree\leaf(5),
                    ]),
                ]),
            ]),
        ]);

        $result = Tree\depth($tree);

        static::assertSame(4, $result);
    }
}
