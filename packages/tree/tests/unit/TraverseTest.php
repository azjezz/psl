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
        $tree = Tree\tree(['id' => 1, 'label' => 'A'], [
            Tree\leaf(['id' => 2, 'label' => 'B']),
            Tree\leaf(['id' => 3, 'label' => 'C']),
        ]);

        $result = Tree\traverse($tree, static fn(array $value, Closure $traverse): array => [
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
        $tree = Tree\leaf('value');

        $result = Tree\traverse($tree, static fn(string $value, Closure $traverse): array => [
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
        $tree = Tree\tree('ROOT', [
            Tree\leaf('CHILD'),
        ]);

        $result = Tree\traverse($tree, static fn(string $value, Closure $traverse): array => [
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
        $tree = Tree\tree(1, [
            Tree\tree(2, [
                Tree\tree(3, [
                    Tree\leaf(4),
                ]),
            ]),
        ]);

        $result = Tree\traverse($tree, static fn(int $value, Closure $traverse): array => [
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
        $tree = Tree\tree('a', [
            Tree\tree('b', [Tree\leaf('c')]),
            Tree\leaf('d'),
        ]);

        $result = Tree\traverse($tree, static function (string $value, Closure $traverse) use ($tree): array {
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
        $tree = Tree\tree('root', [
            Tree\leaf('a'),
            Tree\leaf('b'),
        ]);

        $result = Tree\traverse($tree, static function (string $value, Closure $traverse): string {
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
        $tree = Tree\tree('parent', [
            Tree\leaf('first'),
            Tree\leaf('second'),
            Tree\leaf('third'),
        ]);

        $result = Tree\traverse($tree, static fn(string $value, Closure $traverse): array => [
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
        $tree = Tree\tree('root', [
            Tree\leaf('1'),
            Tree\leaf('2'),
            Tree\leaf('3'),
            Tree\leaf('4'),
        ]);

        $result = Tree\traverse($tree, static fn(string $value, Closure $traverse): array => [
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
