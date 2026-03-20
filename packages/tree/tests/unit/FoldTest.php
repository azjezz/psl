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
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\leaf(3),
        ]);

        $result = Tree\fold($tree, static fn(int $value, array $children): int => $value + array_sum($children));

        static::assertSame(6, $result);
    }

    public function testFoldWithNestedChildren(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [Tree\leaf(3)]),
            Tree\leaf(4),
        ]);

        $result = Tree\fold($tree, static fn(int $value, array $children): int => $value + array_sum($children));

        static::assertSame(10, $result);
    }

    public function testFoldSingleNode(): void
    {
        $tree = Tree\leaf(42);

        $result = Tree\fold($tree, static fn(int $value, array $children): int => $value + array_sum($children));

        static::assertSame(42, $result);
    }

    public function testFoldBuildStructure(): void
    {
        $tree = Tree\tree('root', [
            Tree\leaf('a'),
            Tree\leaf('b'),
        ]);

        $result = Tree\fold($tree, static fn(string $value, array $children): array => [
            'value' => $value,
            'children' => $children,
        ]);

        static::assertSame(
            ['value' => 'root', 'children' => [['value' => 'a', 'children' => []], ['value' => 'b', 'children' => []]]],
            $result,
        );
    }
}
