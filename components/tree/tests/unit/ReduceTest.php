<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ReduceTest extends TestCase
{
    public function testReduceAccumulatesValues(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\tree(3, [Tree\leaf(4)]),
        ]);

        $result = Tree\reduce($tree, static fn(int $acc, int $x): int => $acc + $x, 0);

        static::assertSame(10, $result);
    }

    public function testReduceWithInitialValue(): void
    {
        $tree = Tree\tree(5, [Tree\leaf(10)]);

        $result = Tree\reduce($tree, static fn(int $acc, int $x): int => $acc * $x, 1);

        static::assertSame(50, $result);
    }

    public function testReducePreOrderTraversal(): void
    {
        $tree = Tree\tree('a', [
            Tree\leaf('b'),
            Tree\tree('c', [Tree\leaf('d')]),
        ]);

        $result = Tree\reduce($tree, static fn(string $acc, string $x): string => $acc . $x, '');

        static::assertSame('abcd', $result);
    }

    public function testReduceSingleNode(): void
    {
        $tree = Tree\leaf(42);

        $result = Tree\reduce($tree, static fn(int $acc, int $x): int => $acc + $x, 0);

        static::assertSame(42, $result);
    }
}
