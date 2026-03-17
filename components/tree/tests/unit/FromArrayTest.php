<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class FromArrayTest extends TestCase
{
    public function testFromArrayCreatesLeafNode(): void
    {
        $array = ['value' => 'leaf', 'children' => []];

        $tree = Tree\from_array($array);

        static::assertSame('leaf', $tree->getValue());
        static::assertSame([], $tree->getChildren());
    }

    public function testFromArrayCreatesTreeWithChildren(): void
    {
        $array = [
            'value' => 'root',
            'children' => [
                ['value' => 'child1', 'children' => []],
                ['value' => 'child2', 'children' => []],
            ],
        ];

        $tree = Tree\from_array($array);

        static::assertSame('root', $tree->getValue());
        static::assertCount(2, $tree->getChildren());
        static::assertSame('child1', $tree->getChildren()[0]->getValue());
        static::assertSame('child2', $tree->getChildren()[1]->getValue());
    }

    public function testFromArrayCreatesNestedTree(): void
    {
        $array = [
            'value' => 'root',
            'children' => [
                [
                    'value' => 'branch',
                    'children' => [
                        ['value' => 'leaf', 'children' => []],
                    ],
                ],
            ],
        ];

        $tree = Tree\from_array($array);

        static::assertSame('root', $tree->getValue());
        static::assertCount(1, $tree->getChildren());
        static::assertSame('branch', $tree->getChildren()[0]->getValue());
        static::assertCount(1, $tree->getChildren()[0]->getChildren());
        static::assertSame('leaf', $tree->getChildren()[0]->getChildren()[0]->getValue());
    }

    public function testFromArrayHandlesMissingChildren(): void
    {
        $array = ['value' => 'root'];

        $tree = Tree\from_array($array);

        static::assertSame('root', $tree->getValue());
        static::assertSame([], $tree->getChildren());
    }
}
