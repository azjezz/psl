<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class AnyTest extends TestCase
{
    public function testAnyReturnsTrueWhenPredicateMatches(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\leaf::<int>(3),
            Tree\leaf::<int>(4),
        ]);

        $result = Tree\any::<int>($tree, static fn(int $x): bool => $x > 3);

        static::assertTrue($result);
    }

    public function testAnyReturnsFalseWhenNoMatch(): void
    {
        $tree = Tree\tree::<int>(1, [Tree\leaf::<int>(2), Tree\leaf::<int>(3)]);

        $result = Tree\any::<int>($tree, static fn(int $x): bool => $x > 10);

        static::assertFalse($result);
    }

    public function testAnyReturnsTrueForRootMatch(): void
    {
        $tree = Tree\tree::<int>(10, [Tree\leaf::<int>(1)]);

        $result = Tree\any::<int>($tree, static fn(int $x): bool => $x === 10);

        static::assertTrue($result);
    }

    public function testAnyInSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        static::assertTrue(Tree\any::<int>($tree, static fn(int $x): bool => $x === 42));
        static::assertFalse(Tree\any::<int>($tree, static fn(int $x): bool => $x === 0));
    }
}
