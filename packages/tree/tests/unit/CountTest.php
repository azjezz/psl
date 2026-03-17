<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class CountTest extends TestCase
{
    public function testCountSingleNode(): void
    {
        $tree = Tree\leaf(1);

        $result = Tree\count($tree);

        static::assertSame(1, $result);
    }

    public function testCountMultipleNodes(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\leaf(3),
        ]);

        $result = Tree\count($tree);

        static::assertSame(3, $result);
    }

    public function testCountNestedTree(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [Tree\leaf(3), Tree\leaf(4)]),
            Tree\leaf(5),
        ]);

        $result = Tree\count($tree);

        static::assertSame(5, $result);
    }

    public function testCountDeepTree(): void
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

        $result = Tree\count($tree);

        static::assertSame(5, $result);
    }
}
