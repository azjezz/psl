<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class AllTest extends TestCase
{
    public function testAllReturnsTrueWhenAllMatch(): void
    {
        $tree = Tree\tree(2, [
            Tree\leaf(4),
            Tree\tree(6, [Tree\leaf(8)]),
        ]);

        $result = Tree\all($tree, static fn(int $x): bool => ($x % 2) === 0);

        static::assertTrue($result);
    }

    public function testAllReturnsFalseWhenOneDoesNotMatch(): void
    {
        $tree = Tree\tree(2, [
            Tree\leaf(4),
            Tree\leaf(5),
        ]);

        $result = Tree\all($tree, static fn(int $x): bool => ($x % 2) === 0);

        static::assertFalse($result);
    }

    public function testAllReturnsFalseWhenRootDoesNotMatch(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(4)]);

        $result = Tree\all($tree, static fn(int $x): bool => ($x % 2) === 0);

        static::assertFalse($result);
    }

    public function testAllInSingleNode(): void
    {
        $tree = Tree\leaf(42);

        static::assertTrue(Tree\all($tree, static fn(int $x): bool => $x > 0));
        static::assertFalse(Tree\all($tree, static fn(int $x): bool => $x < 0));
    }
}
