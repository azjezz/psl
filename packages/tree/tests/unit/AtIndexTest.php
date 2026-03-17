<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class AtIndexTest extends TestCase
{
    public function testAtIndexEmptyPathReturnsRoot(): void
    {
        $tree = Tree\tree('a', [Tree\leaf('b')]);

        $result = Tree\at_index($tree, []);

        static::assertSame('a', $result);
    }

    public function testAtIndexFindsDirectChild(): void
    {
        $tree = Tree\tree('a', [
            Tree\leaf('b'),
            Tree\leaf('c'),
        ]);

        static::assertSame('b', Tree\at_index($tree, [0]));
        static::assertSame('c', Tree\at_index($tree, [1]));
    }

    public function testAtIndexFindsNestedChild(): void
    {
        $tree = Tree\tree('a', [
            Tree\tree('b', [Tree\leaf('c')]),
            Tree\leaf('d'),
        ]);

        $result = Tree\at_index($tree, [0, 0]);

        static::assertSame('c', $result);
    }

    public function testAtIndexReturnsNullForInvalidPath(): void
    {
        $tree = Tree\tree('a', [Tree\leaf('b')]);

        static::assertNull(Tree\at_index($tree, [5]));
        static::assertNull(Tree\at_index($tree, [0, 0]));
    }

    public function testAtIndexDeepNesting(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [
                Tree\tree(3, [
                    Tree\leaf(4),
                ]),
            ]),
        ]);

        $result = Tree\at_index($tree, [0, 0, 0]);

        static::assertSame(4, $result);
    }
}
