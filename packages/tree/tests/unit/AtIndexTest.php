<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class AtIndexTest extends TestCase
{
    public function testAtIndexEmptyPathReturnsRoot(): void
    {
        $tree = Tree\tree::<string>('a', [Tree\leaf::<string>('b')]);

        $result = Tree\at_index::<string>($tree, []);

        static::assertSame('a', $result);
    }

    public function testAtIndexFindsDirectChild(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\leaf::<string>('b'),
            Tree\leaf::<string>('c'),
        ]);

        static::assertSame('b', Tree\at_index::<string>($tree, [0]));
        static::assertSame('c', Tree\at_index::<string>($tree, [1]));
    }

    public function testAtIndexFindsNestedChild(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\tree::<string>('b', [Tree\leaf::<string>('c')]),
            Tree\leaf::<string>('d'),
        ]);

        $result = Tree\at_index::<string>($tree, [0, 0]);

        static::assertSame('c', $result);
    }

    public function testAtIndexReturnsNullForInvalidPath(): void
    {
        $tree = Tree\tree::<string>('a', [Tree\leaf::<string>('b')]);

        static::assertNull(Tree\at_index::<string>($tree, [5]));
        static::assertNull(Tree\at_index::<string>($tree, [0, 0]));
    }

    public function testAtIndexDeepNesting(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [
                Tree\tree::<int>(3, [
                    Tree\leaf::<int>(4),
                ]),
            ]),
        ]);

        $result = Tree\at_index::<int>($tree, [0, 0, 0]);

        static::assertSame(4, $result);
    }
}
