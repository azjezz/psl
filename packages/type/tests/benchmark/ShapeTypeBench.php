<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Benchmark;

use ArrayIterator;
use Override;
use PhpBench\Attributes\Groups;
use Psl\Type;

#[Groups(['type'])]
final class ShapeTypeBench extends GenericTypeBench<Type\TypeInterface<array>>
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function provideHappyPathCoercion(): array
    {
        return [
            'empty shape, empty array value' => [
                'type' => Type\shape::<string|int, mixed>([], true),
                'value' => [],
            ],
            'empty shape, empty iterable value' => [
                'type' => Type\shape::<string|int, mixed>([], true),
                'value' => new ArrayIterator([]),
            ],
            'empty shape, non-empty array value' => [
                'type' => Type\shape::<string|int, mixed>([], true),
                'value' => ['foo' => 'bar'],
            ],
            'empty shape, non-empty iterable value' => [
                'type' => Type\shape::<string|int, mixed>([], true),
                'value' => new ArrayIterator(['foo' => 'bar']),
            ],
            'complex shape with optional values, minimum array value' => [
                'type' => Type\shape::<string, mixed>([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional::<mixed>(Type\mixed()),
                ], true),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, minimum iterable value' => [
                'type' => Type\shape::<string, mixed>([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional::<mixed>(Type\mixed()),
                ], true),
                'value' => new ArrayIterator([
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ]),
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => Type\shape::<string, mixed>([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional::<mixed>(Type\mixed()),
                ], true),
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
                'type' => Type\shape::<string, mixed>([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional::<mixed>(Type\mixed()),
                ], true),
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
                'type' => Type\shape::<string, array|string>([
                    'name' => Type\string(),
                    'articles' => Type\vec::<array>(Type\shape::<string, array|int|string>([
                        'title' => Type\string(),
                        'content' => Type\string(),
                        'likes' => Type\int(),
                        'comments' => Type\optional::<array>(Type\vec::<array>(Type\shape::<string, string>([
                            'user' => Type\string(),
                            'comment' => Type\string(),
                        ]))),
                    ])),
                    'dictionary' => Type\dict::<string, array>(
                        Type\string(),
                        Type\vec::<array>(Type\shape::<string, string>([
                            'title' => Type\string(),
                            'content' => Type\string(),
                        ])),
                    ),
                    'pagination' => Type\optional::<array>(Type\shape::<string, int>([
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
                    'dictionary' => [
                        'key' => [[
                            'title' => 'ok',
                            'content' => 'ok',
                        ]],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function provideHappyPathAssertion(): array
    {
        return [
            'empty shape, empty array value' => [
                'type' => Type\shape::<string|int, mixed>([], true),
                'value' => [],
            ],
            'empty shape, non-empty array value' => [
                'type' => Type\shape::<string|int, mixed>([], true),
                'value' => ['foo' => 'bar'],
            ],
            'complex shape with optional values, minimum array value' => [
                'type' => Type\shape::<string, mixed>([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional::<mixed>(Type\mixed()),
                ], true),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => Type\shape::<string, mixed>([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional::<mixed>(Type\mixed()),
                ], true),
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
                'type' => Type\shape::<string, array|string>([
                    'name' => Type\string(),
                    'articles' => Type\vec::<array>(Type\shape::<string, array|int|string>([
                        'title' => Type\string(),
                        'content' => Type\string(),
                        'likes' => Type\int(),
                        'comments' => Type\optional::<array>(Type\vec::<array>(Type\shape::<string, string>([
                            'user' => Type\string(),
                            'comment' => Type\string(),
                        ]))),
                    ])),
                    'dictionary' => Type\dict::<string, array>(
                        Type\string(),
                        Type\vec::<array>(Type\shape::<string, string>([
                            'title' => Type\string(),
                            'content' => Type\string(),
                        ])),
                    ),
                    'pagination' => Type\optional::<array>(Type\shape::<string, int>([
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
                    'dictionary' => [
                        'key' => [[
                            'title' => 'ok',
                            'content' => 'ok',
                        ]],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function provideHappyPathMatches(): array
    {
        // As of now, matches ~= coercion in terms of happy path
        return $this->provideHappyPathAssertion();
    }
}
