<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class PreOrderTest extends TestCase
{
    public function testPreOrderTraversal(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\tree::<int>(3, [Tree\leaf::<int>(4), Tree\leaf::<int>(5)]),
            Tree\leaf::<int>(6),
        ]);

        $result = Tree\pre_order::<int>($tree);

        static::assertSame([1, 2, 3, 4, 5, 6], $result);
    }

    public function testPreOrderSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        $result = Tree\pre_order::<int>($tree);

        static::assertSame([42], $result);
    }

    public function testPreOrderDeepTree(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\tree::<string>('b', [
                Tree\tree::<string>('c', [
                    Tree\leaf::<string>('d'),
                ]),
            ]),
        ]);

        $result = Tree\pre_order::<string>($tree);

        static::assertSame(['a', 'b', 'c', 'd'], $result);
    }
}
