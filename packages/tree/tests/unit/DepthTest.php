<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class DepthTest extends TestCase
{
    public function testDepthSingleNode(): void
    {
        $tree = Tree\leaf::<int>(1);

        $result = Tree\depth::<int>($tree);

        static::assertSame(0, $result);
    }

    public function testDepthOneLevel(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\leaf::<int>(3),
        ]);

        $result = Tree\depth::<int>($tree);

        static::assertSame(1, $result);
    }

    public function testDepthMultipleLevels(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [Tree\leaf::<int>(3)]),
            Tree\leaf::<int>(4),
        ]);

        $result = Tree\depth::<int>($tree);

        static::assertSame(2, $result);
    }

    public function testDepthTreeNodeWithNoChildren(): void
    {
        $tree = Tree\tree::<int>(1, []);

        $result = Tree\depth::<int>($tree);

        static::assertSame(0, $result);
    }

    public function testDepthDeepTree(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [
                Tree\tree::<int>(3, [
                    Tree\tree::<int>(4, [
                        Tree\leaf::<int>(5),
                    ]),
                ]),
            ]),
        ]);

        $result = Tree\depth::<int>($tree);

        static::assertSame(4, $result);
    }
}
