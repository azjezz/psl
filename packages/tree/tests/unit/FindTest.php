<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class FindTest extends TestCase
{
    public function testFindReturnsMatchingValue(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\tree::<int>(3, [Tree\leaf::<int>(4)]),
            Tree\leaf::<int>(5),
        ]);

        $result = Tree\find::<int>($tree, static fn(int $x): bool => $x === 4);

        static::assertSame(4, $result);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $tree = Tree\tree::<int>(1, [Tree\leaf::<int>(2), Tree\leaf::<int>(3)]);

        $result = Tree\find::<int>($tree, static fn(int $x): bool => $x === 10);

        static::assertNull($result);
    }

    public function testFindReturnsFirstMatch(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(5),
            Tree\leaf::<int>(10),
            Tree\leaf::<int>(15),
        ]);

        $result = Tree\find::<int>($tree, static fn(int $x): bool => $x > 3);

        static::assertSame(5, $result);
    }

    public function testFindInSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        $result = Tree\find::<int>($tree, static fn(int $x): bool => $x === 42);

        static::assertSame(42, $result);
    }
}
