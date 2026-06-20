<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ContainsTest extends TestCase
{
    public function testContainsReturnsTrueWhenValueExists(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\tree::<int>(3, [Tree\leaf::<int>(4)]),
        ]);

        static::assertTrue(Tree\contains::<int>($tree, 4));
        static::assertTrue(Tree\contains::<int>($tree, 1));
        static::assertTrue(Tree\contains::<int>($tree, 3));
    }

    public function testContainsReturnsFalseWhenValueDoesNotExist(): void
    {
        $tree = Tree\tree::<int>(1, [Tree\leaf::<int>(2), Tree\leaf::<int>(3)]);

        static::assertFalse(Tree\contains::<int>($tree, 10));
        static::assertFalse(Tree\contains::<int>($tree, 0));
    }

    public function testContainsUsesStrictComparison(): void
    {
        $tree = Tree\tree::<string>('1', [Tree\leaf::<string>('2')]);

        static::assertFalse(Tree\contains::<string>($tree, 1));
        static::assertTrue(Tree\contains::<string>($tree, '1'));
    }

    public function testContainsInSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        static::assertTrue(Tree\contains::<int>($tree, 42));
        static::assertFalse(Tree\contains::<int>($tree, 0));
    }
}
