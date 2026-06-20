<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Tree;

final class ToArrayTest extends TestCase
{
    public function testToArrayConvertsLeafNode(): void
    {
        $tree = Tree\leaf::<string>('value');

        $result = Tree\to_array::<string>($tree);

        static::assertSame(['value' => 'value', 'children' => []], $result);
    }

    public function testToArrayConvertsTreeWithChildren(): void
    {
        $tree = Tree\tree::<string>('root', [
            Tree\leaf::<string>('child1'),
            Tree\leaf::<string>('child2'),
        ]);

        $result = Tree\to_array::<string>($tree);

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
        $tree = Tree\tree::<string>('root', [
            Tree\tree::<string>('branch', [
                Tree\leaf::<string>('leaf'),
            ]),
        ]);

        $result = Tree\to_array::<string>($tree);

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

        $tree = Tree\from_array::<string>($original);
        $result = Tree\to_array::<string>($tree);

        static::assertSame($original, $result);
    }
}
