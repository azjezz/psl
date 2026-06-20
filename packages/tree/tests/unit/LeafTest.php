<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class LeafTest extends TestCase
{
    public function testLeafCreatesLeafNode(): void
    {
        $leaf = Tree\leaf::<string>('value');

        static::assertInstanceOf(Tree\LeafNode::class, $leaf);
        static::assertSame('value', $leaf->getValue());
        static::assertTrue(Tree\is_leaf::<string>($leaf));
    }

    public function testLeafNodeJsonSerialize(): void
    {
        $leaf = Tree\leaf::<string>('value');

        $array = $leaf->jsonSerialize();

        static::assertSame('value', $array['value']);
    }
}
