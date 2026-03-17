<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ToArrayTest extends TestCase
{
    public function testToArrayConvertsLeafNode(): void
    {
        $tree = Tree\leaf('value');

        $result = Tree\to_array($tree);

        static::assertSame(['value' => 'value', 'children' => []], $result);
    }

    public function testToArrayConvertsTreeWithChildren(): void
    {
        $tree = Tree\tree('root', [
            Tree\leaf('child1'),
            Tree\leaf('child2'),
        ]);

        $result = Tree\to_array($tree);

        static::assertSame(
            [
                'value' => 'root',
                'children' => [
                    ['value' => 'child1', 'children' => []],
                    ['value' => 'child2', 'children' => []],
                ],
            ],
            $result,
        );
    }

    public function testToArrayConvertsNestedTree(): void
    {
        $tree = Tree\tree('root', [
            Tree\tree('branch', [
                Tree\leaf('leaf'),
            ]),
        ]);

        $result = Tree\to_array($tree);

        static::assertSame(
            [
                'value' => 'root',
                'children' => [
                    [
                        'value' => 'branch',
                        'children' => [
                            ['value' => 'leaf', 'children' => []],
                        ],
                    ],
                ],
            ],
            $result,
        );
    }

    public function testToArrayRoundTrip(): void
    {
        $original = [
            'value' => 'root',
            'children' => [
                ['value' => 'a', 'children' => []],
                [
                    'value' => 'b',
                    'children' => [
                        ['value' => 'c', 'children' => []],
                    ],
                ],
            ],
        ];

        $tree = Tree\from_array($original);
        $result = Tree\to_array($tree);

        static::assertSame($original, $result);
    }
}
