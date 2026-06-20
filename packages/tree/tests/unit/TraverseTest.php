<?php

declare(strict_types=1);

namespace Psl\Tree\Tests\Unit;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Tree;

use function count;
use function implode;
use function strtolower;

final class TraverseTest extends TestCase
{
    public function testTraverseWithCustomChildrenProperty(): void
    {
        $tree = Tree\tree::<array>(['id' => 1, 'label' => 'A'], [
            Tree\leaf::<array>(['id' => 2, 'label' => 'B']),
            Tree\leaf::<array>(['id' => 3, 'label' => 'C']),
        ]);

        $result = Tree\traverse::<array, array>($tree, static fn(array $value, Closure $traverse): array => [
            'id' => $value['id'],
            'label' => $value['label'],
            'customChildrenProp' => $traverse(),
        ]);

        static::assertSame(
            [
                'id' => 1,
                'label' => 'A',
                'customChildrenProp' => [
                    ['id' => 2, 'label' => 'B', 'customChildrenProp' => []],
                    ['id' => 3, 'label' => 'C', 'customChildrenProp' => []],
                ],
            ],
            $result,
        );
    }

    public function testTraverseLeafNode(): void
    {
        $tree = Tree\leaf::<string>('value');

        $result = Tree\traverse::<string, array>($tree, static fn(string $value, Closure $traverse): array => [
            'value' => $value,
            'children' => $traverse(),
        ]);

        static::assertSame(
            [
                'value' => 'value',
                'children' => [],
            ],
            $result,
        );
    }

    public function testTraverseWithValueTransformation(): void
    {
        $tree = Tree\tree::<string>('ROOT', [
            Tree\leaf::<string>('CHILD'),
        ]);

        $result = Tree\traverse::<string, array>($tree, static fn(string $value, Closure $traverse): array => [
            'name' => strtolower($value),
            'children' => $traverse(),
        ]);

        static::assertSame(
            [
                'name' => 'root',
                'children' => [
                    ['name' => 'child', 'children' => []],
                ],
            ],
            $result,
        );
    }

    public function testTraverseDeepNesting(): void
    {
        $tree = Tree\tree::<int>(1, [
            Tree\tree::<int>(2, [
                Tree\tree::<int>(3, [
                    Tree\leaf::<int>(4),
                ]),
            ]),
        ]);

        $result = Tree\traverse::<int, array>($tree, static fn(int $value, Closure $traverse): array => [
            'v' => $value,
            'c' => $traverse(),
        ]);

        static::assertSame(
            [
                'v' => 1,
                'c' => [
                    [
                        'v' => 2,
                        'c' => [
                            [
                                'v' => 3,
                                'c' => [
                                    ['v' => 4, 'c' => []],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $result,
        );
    }

    public function testTraverseWithConditionalChildren(): void
    {
        $tree = Tree\tree::<string>('a', [
            Tree\tree::<string>('b', [Tree\leaf::<string>('c')]),
            Tree\leaf::<string>('d'),
        ]);

        $result = Tree\traverse::<string, array>($tree, static function (string $value, Closure $traverse) use ($tree): array {
            $children = $traverse();
            return [
                'name' => $value,
                'hasChildren' => [] !== $children,
                'childCount' => count($children),
                'children' => $children,
            ];
        });

        static::assertSame(
            [
                'name' => 'a',
                'hasChildren' => true,
                'childCount' => 2,
                'children' => [
                    [
                        'name' => 'b',
                        'hasChildren' => true,
                        'childCount' => 1,
                        'children' => [
                            ['name' => 'c', 'hasChildren' => false, 'childCount' => 0, 'children' => []],
                        ],
                    ],
                    ['name' => 'd', 'hasChildren' => false, 'childCount' => 0, 'children' => []],
                ],
            ],
            $result,
        );
    }

    public function testTraverseToStringRepresentation(): void
    {
        $tree = Tree\tree::<string>('root', [
            Tree\leaf::<string>('a'),
            Tree\leaf::<string>('b'),
        ]);

        $result = Tree\traverse::<string, string>($tree, static function (string $value, Closure $traverse): string {
            $children = $traverse();
            if ([] === $children) {
                return $value;
            }

            return $value . '(' . implode(',', $children) . ')';
        });

        static::assertSame('root(a,b)', $result);
    }

    public function testTraverseWithIndexedChildren(): void
    {
        $tree = Tree\tree::<string>('parent', [
            Tree\leaf::<string>('first'),
            Tree\leaf::<string>('second'),
            Tree\leaf::<string>('third'),
        ]);

        $result = Tree\traverse::<string, array>($tree, static fn(string $value, Closure $traverse): array => [
            'label' => $value,
            'items' => $traverse(),
        ]);

        static::assertSame(
            [
                'label' => 'parent',
                'items' => [
                    ['label' => 'first', 'items' => []],
                    ['label' => 'second', 'items' => []],
                    ['label' => 'third', 'items' => []],
                ],
            ],
            $result,
        );
    }

    public function testTraversePreservesOrder(): void
    {
        $tree = Tree\tree::<string>('root', [
            Tree\leaf::<string>('1'),
            Tree\leaf::<string>('2'),
            Tree\leaf::<string>('3'),
            Tree\leaf::<string>('4'),
        ]);

        $result = Tree\traverse::<string, array>($tree, static fn(string $value, Closure $traverse): array => [
            'v' => $value,
            'c' => $traverse(),
        ]);

        static::assertSame(
            [
                'v' => 'root',
                'c' => [
                    ['v' => '1', 'c' => []],
                    ['v' => '2', 'c' => []],
                    ['v' => '3', 'c' => []],
                    ['v' => '4', 'c' => []],
                ],
            ],
            $result,
        );
    }
}
