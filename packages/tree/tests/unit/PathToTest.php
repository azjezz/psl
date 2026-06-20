<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class PathToTest extends TestCase
{
    public function testPathToFindsPathToLeaf(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\tree::<string>('b', [Tree\leaf::<string>('c')]),
            Tree\leaf::<string>('d'),
        ]);

        $result = Tree\path_to::<string>($tree, static fn(string $x): bool => $x === 'c');

        static::assertSame(['a', 'b', 'c'], $result);
    }

    public function testPathToReturnsNullWhenNotFound(): void
    {
        $tree = Tree\tree::<int>(1, [Tree\leaf::<int>(2), Tree\leaf::<int>(3)]);

        $result = Tree\path_to::<int>($tree, static fn(int $x): bool => $x === 10);

        static::assertNull($result);
    }

    public function testPathToFindsRoot(): void
    {
        $tree = Tree\tree::<int>(42, [Tree\leaf::<int>(1)]);

        $result = Tree\path_to::<int>($tree, static fn(int $x): bool => $x === 42);

        static::assertSame([42], $result);
    }

    public function testPathToFindsFirstMatch(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(5),
            Tree\tree::<int>(2, [Tree\leaf::<int>(5)]),
        ]);

        $result = Tree\path_to::<int>($tree, static fn(int $x): bool => $x === 5);

        static::assertSame([1, 5], $result);
    }

    public function testPathToDeepPath(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [
                Tree\tree::<int>(3, [
                    Tree\leaf::<int>(4),
                ]),
            ]),
        ]);

        $result = Tree\path_to::<int>($tree, static fn(int $x): bool => $x === 4);

        static::assertSame([1, 2, 3, 4], $result);
    }
}
