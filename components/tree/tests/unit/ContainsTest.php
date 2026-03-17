<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ContainsTest extends TestCase
{
    public function testContainsReturnsTrueWhenValueExists(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\tree(3, [Tree\leaf(4)]),
        ]);

        static::assertTrue(Tree\contains($tree, 4));
        static::assertTrue(Tree\contains($tree, 1));
        static::assertTrue(Tree\contains($tree, 3));
    }

    public function testContainsReturnsFalseWhenValueDoesNotExist(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);

        static::assertFalse(Tree\contains($tree, 10));
        static::assertFalse(Tree\contains($tree, 0));
    }

    public function testContainsUsesStrictComparison(): void
    {
        $tree = Tree\tree('1', [Tree\leaf('2')]);

        static::assertFalse(Tree\contains($tree, 1));
        static::assertTrue(Tree\contains($tree, '1'));
    }

    public function testContainsInSingleNode(): void
    {
        $tree = Tree\leaf(42);

        static::assertTrue(Tree\contains($tree, 42));
        static::assertFalse(Tree\contains($tree, 0));
    }
}
