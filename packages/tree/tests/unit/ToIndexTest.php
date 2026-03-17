<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ToIndexTest extends TestCase
{
    public function testToIndexFindsRootNode(): void
    {
        $tree = Tree\tree('a', [Tree\leaf('b')]);

        $result = Tree\to_index($tree, static fn(string $x): bool => $x === 'a');

        static::assertSame([], $result);
    }

    public function testToIndexFindsDirectChild(): void
    {
        $tree = Tree\tree('a', [
            Tree\leaf('b'),
            Tree\leaf('c'),
        ]);

        static::assertSame([0], Tree\to_index($tree, static fn(string $x): bool => $x === 'b'));
        static::assertSame([1], Tree\to_index($tree, static fn(string $x): bool => $x === 'c'));
    }

    public function testToIndexFindsNestedChild(): void
    {
        $tree = Tree\tree('a', [
            Tree\tree('b', [Tree\leaf('c')]),
            Tree\leaf('d'),
        ]);

        $result = Tree\to_index($tree, static fn(string $x): bool => $x === 'c');

        static::assertSame([0, 0], $result);
    }

    public function testToIndexReturnsNullWhenNotFound(): void
    {
        $tree = Tree\tree('a', [Tree\leaf('b')]);

        $result = Tree\to_index($tree, static fn(string $x): bool => $x === 'z');

        static::assertNull($result);
    }

    public function testToIndexDeepNesting(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [
                Tree\tree(3, [
                    Tree\leaf(4),
                ]),
            ]),
        ]);

        $result = Tree\to_index($tree, static fn(int $x): bool => $x === 4);

        static::assertSame([0, 0, 0], $result);
    }

    public function testToIndexFindsFirstMatch(): void
    {
        $tree = Tree\tree('a', [
            Tree\leaf('x'),
            Tree\leaf('x'),
            Tree\leaf('x'),
        ]);

        // Should find the first match at index 0
        $result = Tree\to_index($tree, static fn(string $x): bool => $x === 'x');

        static::assertSame([0], $result);
    }

    public function testToIndexWithComplexPredicate(): void
    {
        $tree = Tree\tree(['id' => 1], [
            Tree\tree(['id' => 2], [
                Tree\leaf(['id' => 3]),
            ]),
            Tree\leaf(['id' => 4]),
        ]);

        $result = Tree\to_index($tree, static fn(array $x): bool => $x['id'] === 3);

        static::assertSame([0, 0], $result);
    }

    public function testToIndexWithMultipleLevels(): void
    {
        $tree = Tree\tree('root', [
            Tree\tree('a', [
                Tree\leaf('a1'),
                Tree\leaf('a2'),
            ]),
            Tree\tree('b', [
                Tree\leaf('b1'),
                Tree\leaf('b2'),
            ]),
            Tree\leaf('c'),
        ]);

        static::assertSame([0, 1], Tree\to_index($tree, static fn(string $x): bool => $x === 'a2'));
        static::assertSame([1, 0], Tree\to_index($tree, static fn(string $x): bool => $x === 'b1'));
        static::assertSame([2], Tree\to_index($tree, static fn(string $x): bool => $x === 'c'));
    }
}
