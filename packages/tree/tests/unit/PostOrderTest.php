<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class PostOrderTest extends TestCase
{
    public function testPostOrderTraversal(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\tree::<int>(3, [Tree\leaf::<int>(4), Tree\leaf::<int>(5)]),
            Tree\leaf::<int>(6),
        ]);

        $result = Tree\post_order::<int>($tree);

        static::assertSame([2, 4, 5, 3, 6, 1], $result);
    }

    public function testPostOrderSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        $result = Tree\post_order::<int>($tree);

        static::assertSame([42], $result);
    }

    public function testPostOrderDeepTree(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\tree::<string>('b', [
                Tree\tree::<string>('c', [
                    Tree\leaf::<string>('d'),
                ]),
            ]),
        ]);

        $result = Tree\post_order::<string>($tree);

        static::assertSame(['d', 'c', 'b', 'a'], $result);
    }
}
