<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class FilterTest extends TestCase
{
    public function testFilterRemovesNodesNotMatchingPredicate(): void
    {
        $tree = Tree\tree(2, [
            Tree\leaf(1),
            Tree\leaf(3),
            Tree\tree(4, [Tree\leaf(5)]),
        ]);

        $result = Tree\filter($tree, static fn(int $x): bool => $x >= 2);

        static::assertNotNull($result);
        static::assertSame(2, $result->getValue());
        static::assertCount(2, $result->getChildren());
        static::assertSame(3, $result->getChildren()[0]->getValue());
        static::assertSame(4, $result->getChildren()[1]->getValue());
    }

    public function testFilterReturnsNullWhenRootDoesNotMatch(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2)]);

        $result = Tree\filter($tree, static fn(int $x): bool => $x > 1);

        static::assertNull($result);
    }
}
