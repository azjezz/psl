<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;
use Psl\Type;

/**
 * @extends GenericTypeBench<Type\TypeInterface<array>>
 */
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
                'type' => Type\shape([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional(Type\mixed()),
                ], true),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, minimum iterable value' => [
                'type' => Type\shape([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional(Type\mixed()),
                ], true),
                'value' => new ArrayIterator([
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ]),
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => Type\shape([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional(Type\mixed()),
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
                'type' => Type\shape([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional(Type\mixed()),
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
                'type' => Type\shape([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional(Type\mixed()),
                ], true),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => Type\shape([
                    'foo' => Type\mixed(),
                    'bar' => Type\mixed(),
                    'baz' => Type\mixed(),
                    'tab' => Type\optional(Type\mixed()),
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
