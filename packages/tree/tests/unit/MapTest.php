<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class MapTest extends TestCase
{
    public function testMapAppliesFunctionToAllNodes(): void
    {
        $tree = Tree\tree(1, [
            Tree\leaf(2),
            Tree\tree(3, [Tree\leaf(4)]),
        ]);

        $result = Tree\map($tree, static fn(int $x): int => $x * 2);

        static::assertSame(2, $result->getValue());
        static::assertSame(4, $result->getChildren()[0]->getValue());
        static::assertSame(6, $result->getChildren()[1]->getValue());
        static::assertSame(8, $result->getChildren()[1]->getChildren()[0]->getValue());
    }

    public function testMapChangesType(): void
    {
        $tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);

        $result = Tree\map($tree, static fn(int $x): string => (string) $x);

        static::assertSame('1', $result->getValue());
        static::assertSame('2', $result->getChildren()[0]->getValue());
    }
}
