<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ToIndexTest extends TestCase
{
    public function testToIndexFindsRootNode(): void
    {
        $tree = Tree\tree::<string>('a', [Tree\leaf::<string>('b')]);

        $result = Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'a');

        static::assertSame([], $result);
    }

    public function testToIndexFindsDirectChild(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\leaf::<string>('b'),
            Tree\leaf::<string>('c'),
        ]);

        static::assertSame([0], Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'b'));
        static::assertSame([1], Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'c'));
    }

    public function testToIndexFindsNestedChild(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\tree::<string>('b', [Tree\leaf::<string>('c')]),
            Tree\leaf::<string>('d'),
        ]);

        $result = Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'c');

        static::assertSame([0, 0], $result);
    }

    public function testToIndexReturnsNullWhenNotFound(): void
    {
        $tree = Tree\tree::<string>('a', [Tree\leaf::<string>('b')]);

        $result = Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'z');

        static::assertNull($result);
    }

    public function testToIndexDeepNesting(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [
                Tree\tree::<int>(3, [
                    Tree\leaf::<int>(4),
                ]),
            ]),
        ]);

        $result = Tree\to_index::<int>($tree, static fn(int $x): bool => $x === 4);

        static::assertSame([0, 0, 0], $result);
    }

    public function testToIndexFindsFirstMatch(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\leaf::<string>('x'),
            Tree\leaf::<string>('x'),
            Tree\leaf::<string>('x'),
        ]);

        // Should find the first match at index 0
        $result = Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'x');

        static::assertSame([0], $result);
    }

    public function testToIndexWithComplexPredicate(): void
    {
        $tree = Tree\tree::<array>(['id' => 1], [
            Tree\tree::<array>(['id' => 2], [
                Tree\leaf::<array>(['id' => 3]),
            ]),
            Tree\leaf::<array>(['id' => 4]),
        ]);

        $result = Tree\to_index::<array>($tree, static fn(array $x): bool => $x['id'] === 3);

        static::assertSame([0, 0], $result);
    }

    public function testToIndexWithMultipleLevels(): void
    {
        $tree = Tree\tree::<string>('root', [
            Tree\tree::<string>('a', [
                Tree\leaf::<string>('a1'),
                Tree\leaf::<string>('a2'),
            ]),
            Tree\tree::<string>('b', [
                Tree\leaf::<string>('b1'),
                Tree\leaf::<string>('b2'),
            ]),
            Tree\leaf::<string>('c'),
        ]);

        static::assertSame([0, 1], Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'a2'));
        static::assertSame([1, 0], Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'b1'));
        static::assertSame([2], Tree\to_index::<string>($tree, static fn(string $x): bool => $x === 'c'));
    }
}
