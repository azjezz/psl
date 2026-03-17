<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class AnyTest extends TestCase
{
    public function testAnyReturnsTrueWhenPredicateMatches(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\leaf(3),
            Tree\leaf(4),
        ]);

        $result = Tree\any($tree, static fn(int $x): bool => $x > 3);

        static::assertTrue($result);
    }

    public function testAnyReturnsFalseWhenNoMatch(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);

        $result = Tree\any($tree, static fn(int $x): bool => $x > 10);

        static::assertFalse($result);
    }

    public function testAnyReturnsTrueForRootMatch(): void
    {
        $tree = Tree\tree(10, [Tree\leaf(1)]);

        $result = Tree\any($tree, static fn(int $x): bool => $x === 10);

        static::assertTrue($result);
    }

    public function testAnyInSingleNode(): void
    {
        $tree = Tree\leaf(42);

        static::assertTrue(Tree\any($tree, static fn(int $x): bool => $x === 42));
        static::assertFalse(Tree\any($tree, static fn(int $x): bool => $x === 0));
    }
}
