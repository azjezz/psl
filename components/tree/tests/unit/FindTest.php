<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class FindTest extends TestCase
{
    public function testFindReturnsMatchingValue(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\tree(3, [Tree\leaf(4)]),
            Tree\leaf(5),
        ]);

        $result = Tree\find($tree, static fn(int $x): bool => $x === 4);

        static::assertSame(4, $result);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);

        $result = Tree\find($tree, static fn(int $x): bool => $x === 10);

        static::assertNull($result);
    }

    public function testFindReturnsFirstMatch(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(5),
            Tree\leaf(10),
            Tree\leaf(15),
        ]);

        $result = Tree\find($tree, static fn(int $x): bool => $x > 3);

        static::assertSame(5, $result);
    }

    public function testFindInSingleNode(): void
    {
        $tree = Tree\leaf(42);

        $result = Tree\find($tree, static fn(int $x): bool => $x === 42);

        static::assertSame(42, $result);
    }
}
