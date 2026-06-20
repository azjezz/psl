<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class AllTest extends TestCase
{
    public function testAllReturnsTrueWhenAllMatch(): void
    {
        $tree = Tree\tree::<int>(2, [
            Tree\leaf::<int>(4),
            Tree\tree::<int>(6, [Tree\leaf::<int>(8)]),
        ]);

        $result = Tree\all::<int>($tree, static fn(int $x): bool => ($x % 2) === 0);

        static::assertTrue($result);
    }

    public function testAllReturnsFalseWhenOneDoesNotMatch(): void
    {
        $tree = Tree\tree::<int>(2, [
            Tree\leaf::<int>(4),
            Tree\leaf::<int>(5),
        ]);

        $result = Tree\all::<int>($tree, static fn(int $x): bool => ($x % 2) === 0);

        static::assertFalse($result);
    }

    public function testAllReturnsFalseWhenRootDoesNotMatch(): void
    {
        $tree = Tree\tree::<int>(1, [Tree\leaf::<int>(2), Tree\leaf::<int>(4)]);

        $result = Tree\all::<int>($tree, static fn(int $x): bool => ($x % 2) === 0);

        static::assertFalse($result);
    }

    public function testAllInSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        static::assertTrue(Tree\all::<int>($tree, static fn(int $x): bool => $x > 0));
        static::assertFalse(Tree\all::<int>($tree, static fn(int $x): bool => $x < 0));
    }
}
