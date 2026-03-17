<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Iterator;
use Override;
use Psl\Collection\CollectionInterface;
use Psl\Collection\IndexAccessInterface;
use Psl\Type;

final class IntersectionTypeTest extends TypeTestCase
{
    public function testIntersectionLeft(): void
    {
        $intersection = Type\intersection(Type\array_key(), Type\int(), Type\positive_int());

        static::assertSame(1, $intersection->coerce('1'));
    }

    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\intersection(Type\int(), Type\array_key());
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [1, 1];
        yield ['1', 1];
        yield ['123', 123];
        yield [static::stringable('123'), 123];
        yield [static::stringable('000'), 0];
        yield [static::stringable('0007'), 7];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['hello'];
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [
            Type\intersection(
                Type\instance_of(IndexAccessInterface::class),
                Type\instance_of(CollectionInterface::class),
            ),
            'Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface',
        ];

        yield [
            Type\intersection(
                Type\instance_of(IndexAccessInterface::class),
                Type\union(Type\instance_of(CollectionInterface::class), Type\instance_of(Iterator::class)),
            ),
            'Psl\Collection\IndexAccessInterface&(Psl\Collection\CollectionInterface|Iterator)',
        ];

        yield [
            Type\intersection(
                Type\union(Type\instance_of(CollectionInterface::class), Type\instance_of(Iterator::class)),
                Type\instance_of(IndexAccessInterface::class),
            ),
            '(Psl\Collection\CollectionInterface|Iterator)&Psl\Collection\IndexAccessInterface',
        ];

        yield [
            Type\intersection(
                Type\instance_of(IndexAccessInterface::class),
                Type\instance_of(CollectionInterface::class),
                Type\instance_of(Iterator::class),
                Type\shape(['id' => Type\string()]),
            ),
            'Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface&Iterator&array{\'id\': string}',
        ];
    }
}
