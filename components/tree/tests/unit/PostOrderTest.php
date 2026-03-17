<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class PostOrderTest extends TestCase
{
    public function testPostOrderTraversal(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\tree(3, [Tree\leaf(4), Tree\leaf(5)]),
            Tree\leaf(6),
        ]);

        $result = Tree\post_order($tree);

        static::assertSame([2, 4, 5, 3, 6, 1], $result);
    }

    public function testPostOrderSingleNode(): void
    {
        $tree = Tree\leaf(42);

        $result = Tree\post_order($tree);

        static::assertSame([42], $result);
    }

    public function testPostOrderDeepTree(): void
    {
        $tree = Tree\tree('a', [
            Tree\tree('b', [
                Tree\tree('c', [
                    Tree\leaf('d'),
                ]),
            ]),
        ]);

        $result = Tree\post_order($tree);

        static::assertSame(['d', 'c', 'b', 'a'], $result);
    }
}
