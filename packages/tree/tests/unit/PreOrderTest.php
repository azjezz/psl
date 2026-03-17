<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class PreOrderTest extends TestCase
{
    public function testPreOrderTraversal(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\tree(3, [Tree\leaf(4), Tree\leaf(5)]),
            Tree\leaf(6),
        ]);

        $result = Tree\pre_order($tree);

        static::assertSame([1, 2, 3, 4, 5, 6], $result);
    }

    public function testPreOrderSingleNode(): void
    {
        $tree = Tree\leaf(42);

        $result = Tree\pre_order($tree);

        static::assertSame([42], $result);
    }

    public function testPreOrderDeepTree(): void
    {
        $tree = Tree\tree('a', [
            Tree\tree('b', [
                Tree\tree('c', [
                    Tree\leaf('d'),
                ]),
            ]),
        ]);

        $result = Tree\pre_order($tree);

        static::assertSame(['a', 'b', 'c', 'd'], $result);
    }
}
