<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;
use PhpBench\Attributes\Groups;
use Psl\Type;

/**
 * @extends GenericTypeBench<Type\TypeInterface<array>>
 */
#[Groups(['type'])]
final class ShapeTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    public function provideHappyPathCoercion(): array
    {
        return [
            'empty shape, empty array value' => [
                'type' => Type\shape([], true),
                'value' => [],
            ],
            'empty shape, empty iterable value' => [
                'type' => Type\shape([], true),
                'value' => new ArrayIterator([]),
            ],
            'empty shape, non-empty array value' => [
                'type' => Type\shape([], true),
                'value' => ['foo' => 'bar'],
            ],
            'empty shape, non-empty iterable value' => [
                'type' => Type\shape([], true),
                'value' => new ArrayIterator(['foo' => 'bar']),
            ],
            'complex shape with optional values, minimum array value' => [
                'type' => Type\shape(
                    [
                        'foo' => Type\mixed(),
                        'bar' => Type\mixed(),
                        'baz' => Type\mixed(),
                        'tab' => Type\optional(Type\mixed()),
                    ],
                    true,
                ),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, minimum iterable value' => [
                'type' => Type\shape(
                    [
                        'foo' => Type\mixed(),
                        'bar' => Type\mixed(),
                        'baz' => Type\mixed(),
                        'tab' => Type\optional(Type\mixed()),
                    ],
                    true,
                ),
                'value' => new ArrayIterator([
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ]),
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => Type\shape(
                    [
                        'foo' => Type\mixed(),
                        'bar' => Type\mixed(),
                        'baz' => Type\mixed(),
                        'tab' => Type\optional(Type\mixed()),
                    ],
                    true,
                ),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                    'tab' => null,
                    'taz' => null,
                    'tar' => null,
                    'waz' => null,
                    'war' => null,
                ],
            ],
            'complex shape with optional values, iterable value with further values' => [
                'type' => Type\shape(
                    [
                        'foo' => Type\mixed(),
                        'bar' => Type\mixed(),
                        'baz' => Type\mixed(),
                        'tab' => Type\optional(Type\mixed()),
                    ],
                    true,
                ),
                'value' => new ArrayIterator([
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                    'tab' => null,
                    'taz' => null,
                    'tar' => null,
                    'waz' => null,
                    'war' => null,
                ]),
            ],
            'real-life-type-usage' => [
                'type' => Type\shape([
                    'name' => Type\string(),
                    'articles' => Type\vec(Type\shape([
                        'title' => Type\string(),
                        'content' => Type\string(),
                        'likes' => Type\int(),
                        'comments' => Type\optional(Type\vec(Type\shape([
                            'user' => Type\string(),
                            'comment' => Type\string(),
                        ]))),
                    ])),
                    'dictionary' => Type\dict(
                        Type\string(),
                        Type\vec(Type\shape([
                            'title' => Type\string(),
                            'content' => Type\string(),
                        ])),
                    ),
                    'pagination' => Type\optional(Type\shape([
                        'currentPage' => Type\uint(),
                        'totalPages' => Type\uint(),
                        'perPage' => Type\uint(),
                        'totalRows' => Type\uint(),
                    ])),
                ]),
                'value' => [
                    'name' => 'ok',
                    'articles' => [[
                        'title' => 'ok',
                        'content' => 'ok',
                        'likes' => 1,
                        'comments' => [
                            [
                                'user' => 'ok',
                                'comment' => 'ok',
                            ],
                            [
                                'user' => 'ok',
                                'comment' => 'ok',
                            ],
                        ],
                    ]],
                    'dictionary' => ['key' => [[
                        'title' => 'ok',
                        'content' => 'ok',
                    ]]],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideHappyPathAssertion(): array
    {
        return [
            'empty shape, empty array value' => [
                'type' => Type\shape([], true),
                'value' => [],
            ],
            'empty shape, non-empty array value' => [
                'type' => Type\shape([], true),
                'value' => ['foo' => 'bar'],
            ],
            'complex shape with optional values, minimum array value' => [
                'type' => Type\shape(
                    [
                        'foo' => Type\mixed(),
                        'bar' => Type\mixed(),
                        'baz' => Type\mixed(),
                        'tab' => Type\optional(Type\mixed()),
                    ],
                    true,
                ),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => Type\shape(
                    [
                        'foo' => Type\mixed(),
                        'bar' => Type\mixed(),
                        'baz' => Type\mixed(),
                        'tab' => Type\optional(Type\mixed()),
                    ],
                    true,
                ),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                    'tab' => null,
                    'taz' => null,
                    'tar' => null,
                    'waz' => null,
                    'war' => null,
                ],
            ],
            'real-life-type-usage' => [
                'type' => Type\shape([
                    'name' => Type\string(),
                    'articles' => Type\vec(Type\shape([
                        'title' => Type\string(),
                        'content' => Type\string(),
                        'likes' => Type\int(),
                        'comments' => Type\optional(Type\vec(Type\shape([
                            'user' => Type\string(),
                            'comment' => Type\string(),
                        ]))),
                    ])),
                    'dictionary' => Type\dict(
                        Type\string(),
                        Type\vec(Type\shape([
                            'title' => Type\string(),
                            'content' => Type\string(),
                        ])),
                    ),
                    'pagination' => Type\optional(Type\shape([
                        'currentPage' => Type\uint(),
                        'totalPages' => Type\uint(),
                        'perPage' => Type\uint(),
                        'totalRows' => Type\uint(),
                    ])),
                ]),
                'value' => [
                    'name' => 'ok',
                    'articles' => [[
                        'title' => 'ok',
                        'content' => 'ok',
                        'likes' => 1,
                        'comments' => [
                            [
                                'user' => 'ok',
                                'comment' => 'ok',
                            ],
                            [
                                'user' => 'ok',
                                'comment' => 'ok',
                            ],
                        ],
                    ]],
                    'dictionary' => ['key' => [[
                        'title' => 'ok',
                        'content' => 'ok',
                    ]]],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideHappyPathMatches(): array
    {
        // As of now, matches ~= coercion in terms of happy path
        return $this->provideHappyPathAssertion();
    }
}
