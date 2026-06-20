<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class CountTest extends TestCase
{
    public function testCountSingleNode(): void
    {
        $tree = Tree\leaf::<int>(1);

        $result = Tree\count::<int>($tree);

        static::assertSame(1, $result);
    }

    public function testCountMultipleNodes(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\leaf::<int>(3),
        ]);

        $result = Tree\count::<int>($tree);

        static::assertSame(3, $result);
    }

    public function testCountNestedTree(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [Tree\leaf::<int>(3), Tree\leaf::<int>(4)]),
            Tree\leaf::<int>(5),
        ]);

        $result = Tree\count::<int>($tree);

        static::assertSame(5, $result);
    }

    public function testCountDeepTree(): void
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

        $result = Tree\count::<int>($tree);

        static::assertSame(5, $result);
    }
}
