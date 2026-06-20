<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ReduceTest extends TestCase
{
    public function testReduceAccumulatesValues(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\tree::<int>(3, [Tree\leaf::<int>(4)]),
        ]);

        $result = Tree\reduce::<int, int>($tree, static fn(int $acc, int $x): int => $acc + $x, 0);

        static::assertSame(10, $result);
    }

    public function testReduceWithInitialValue(): void
    {
        $tree = Tree\tree::<int>(5, [Tree\leaf::<int>(10)]);

        $result = Tree\reduce::<int, int>($tree, static fn(int $acc, int $x): int => $acc * $x, 1);

        static::assertSame(50, $result);
    }

    public function testReducePreOrderTraversal(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\leaf::<string>('b'),
            Tree\tree::<string>('c', [Tree\leaf::<string>('d')]),
        ]);

        $result = Tree\reduce::<string, string>($tree, static fn(string $acc, string $x): string => $acc . $x, '');

        static::assertSame('abcd', $result);
    }

    public function testReduceSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        $result = Tree\reduce::<int, int>($tree, static fn(int $acc, int $x): int => $acc + $x, 0);

        static::assertSame(42, $result);
    }
}
