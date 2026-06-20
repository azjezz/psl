<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class MapTest extends TestCase
{
    public function testMapAppliesFunctionToAllNodes(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\leaf::<int>(2),
            Tree\tree::<int>(3, [Tree\leaf::<int>(4)]),
        ]);

        $result = Tree\map::<int, int>($tree, static fn(int $x): int => $x * 2);

        static::assertSame(2, $result->getValue());
        static::assertSame(4, $result->getChildren()[0]->getValue());
        static::assertSame(6, $result->getChildren()[1]->getValue());
        static::assertSame(8, $result->getChildren()[1]->getChildren()[0]->getValue());
    }

    public function testMapChangesType(): void
    {
        $tree = Tree\tree::<int>(1, [Tree\leaf::<int>(2), Tree\leaf::<int>(3)]);

        $result = Tree\map::<int, string>($tree, static fn(int $x): string => (string) $x);

        static::assertSame('1', $result->getValue());
        static::assertSame('2', $result->getChildren()[0]->getValue());
    }
}
