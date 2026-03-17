<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class PathToTest extends TestCase
{
    public function testPathToFindsPathToLeaf(): void
    {
        $tree = Tree\tree('a', [
            Tree\tree('b', [Tree\leaf('c')]),
            Tree\leaf('d'),
        ]);

        $result = Tree\path_to($tree, static fn(string $x): bool => $x === 'c');

        static::assertSame(['a', 'b', 'c'], $result);
    }

    public function testPathToReturnsNullWhenNotFound(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);

        $result = Tree\path_to($tree, static fn(int $x): bool => $x === 10);

        static::assertNull($result);
    }

    public function testPathToFindsRoot(): void
    {
        $tree = Tree\tree(42, [Tree\leaf(1)]);

        $result = Tree\path_to($tree, static fn(int $x): bool => $x === 42);

        static::assertSame([42], $result);
    }

    public function testPathToFindsFirstMatch(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(5),
            Tree\tree(2, [Tree\leaf(5)]),
        ]);

        $result = Tree\path_to($tree, static fn(int $x): bool => $x === 5);

        static::assertSame([1, 5], $result);
    }

    public function testPathToDeepPath(): void
    {
        $tree = Tree\tree(1, [
            Tree\tree(2, [
                Tree\tree(3, [
                    Tree\leaf(4),
                ]),
            ]),
        ]);

        $result = Tree\path_to($tree, static fn(int $x): bool => $x === 4);

        static::assertSame([1, 2, 3, 4], $result);
    }
}
