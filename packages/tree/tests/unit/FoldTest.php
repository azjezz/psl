<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

use function array_sum;

final class FoldTest extends TestCase
{
    public function testFoldPostOrderProcessing(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\leaf::<int>(3),
        ]);

        $result = Tree\fold::<int, int>($tree, static fn(int $value, array $children): int => $value + array_sum($children));

        static::assertSame(6, $result);
    }

    public function testFoldWithNestedChildren(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [Tree\leaf::<int>(3)]),
            Tree\leaf::<int>(4),
        ]);

        $result = Tree\fold::<int, int>($tree, static fn(int $value, array $children): int => $value + array_sum($children));

        static::assertSame(10, $result);
    }

    public function testFoldSingleNode(): void
    {
        $tree = Tree\leaf::<int>(42);

        $result = Tree\fold::<int, int>($tree, static fn(int $value, array $children): int => $value + array_sum($children));

        static::assertSame(42, $result);
    }

    public function testFoldBuildStructure(): void
    {
        $tree = Tree\tree::<string>('root', [
            Tree\leaf::<string>('a'),
            Tree\leaf::<string>('b'),
        ]);

        $result = Tree\fold::<string, array>($tree, static fn(string $value, array $children): array => [
            'value' => $value,
            'children' => $children,
        ]);

        static::assertSame(
            ['value' => 'root', 'children' => [['value' => 'a', 'children' => []], ['value' => 'b', 'children' => []]]],
            $result,
        );
    }
}
