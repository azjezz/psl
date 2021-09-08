<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Type;

use ArrayIterator;

use function Psl\Type\mixed;
use function Psl\Type\optional;
use function Psl\Type\shape;

/** @extends GenericTypeBench<\Psl\Type\TypeInterface<array>> */
final class ShapeTypeBench extends GenericTypeBench
{
    /**
     * {@inheritDoc}
     */
    public function provideHappyPathCoercion(): array
    {
        return [
            'empty shape, empty array value' => [
                'type' => shape([], true),
                'value' => [],
            ],
            'empty shape, empty iterable value' => [
                'type' => shape([], true),
                'value' => new ArrayIterator([]),
            ],
            'empty shape, non-empty array value' => [
                'type' => shape([], true),
                'value' => ['foo' => 'bar'],
            ],
            'empty shape, non-empty iterable value' => [
                'type' => shape([], true),
                'value' => new ArrayIterator(['foo' => 'bar']),
            ],
            'complex shape with optional values, minimum array value' => [
                'type' => shape([
                    'foo' => mixed(),
                    'bar' => mixed(),
                    'baz' => mixed(),
                    'tab' => optional(mixed()),
                ], true),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, minimum iterable value' => [
                'type' => shape([
                    'foo' => mixed(),
                    'bar' => mixed(),
                    'baz' => mixed(),
                    'tab' => optional(mixed()),
                ], true),
                'value' => new ArrayIterator([
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ]),
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => shape([
                    'foo' => mixed(),
                    'bar' => mixed(),
                    'baz' => mixed(),
                    'tab' => optional(mixed()),
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
                'type' => shape([
                    'foo' => mixed(),
                    'bar' => mixed(),
                    'baz' => mixed(),
                    'tab' => optional(mixed()),
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
                'type' => shape([], true),
                'value' => [],
            ],
            'empty shape, non-empty array value' => [
                'type' => shape([], true),
                'value' => ['foo' => 'bar'],
            ],
            'complex shape with optional values, minimum array value' => [
                'type' => shape([
                    'foo' => mixed(),
                    'bar' => mixed(),
                    'baz' => mixed(),
                    'tab' => optional(mixed()),
                ], true),
                'value' => [
                    'foo' => null,
                    'bar' => null,
                    'baz' => null,
                ],
            ],
            'complex shape with optional values, array value with further values' => [
                'type' => shape([
                    'foo' => mixed(),
                    'bar' => mixed(),
                    'baz' => mixed(),
                    'tab' => optional(mixed()),
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
