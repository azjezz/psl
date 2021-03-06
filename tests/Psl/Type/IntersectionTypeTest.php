<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Iterator;
use Psl\Collection\CollectionInterface;
use Psl\Collection\IndexAccessInterface;
use Psl\Type;

final class IntersectionTypeTest extends TypeTest
{
    public function testIntersectionLeft(): void
    {
        $intersection = Type\intersection(Type\array_key(), Type\int());

        static::assertSame(1, $intersection->coerce('1'));
    }

    public function getType(): Type\TypeInterface
    {
        return Type\intersection(Type\int(), Type\array_key());
    }

    public function getValidCoercions(): iterable
    {
        yield [1, 1];
        yield ['1', 1];
        yield ['123', 123];
        yield [$this->stringable('123'), 123];
        yield [$this->stringable('000'), 0];
        yield [$this->stringable('0007'), 7];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['hello'];
        yield [$this->stringable('foo')];
        yield [new class {
        }];
    }

    public function getToStringExamples(): iterable
    {
        yield [
            Type\intersection(
                Type\object(IndexAccessInterface::class),
                Type\object(CollectionInterface::class)
            ),
            'Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface'
        ];

        yield [Type\intersection(
            Type\object(IndexAccessInterface::class),
            Type\union(Type\object(CollectionInterface::class), Type\object(Iterator::class))
        ), 'Psl\Collection\IndexAccessInterface&(Psl\Collection\CollectionInterface|Iterator)'];

        yield [Type\intersection(
            Type\union(Type\object(CollectionInterface::class), Type\object(Iterator::class)),
            Type\object(IndexAccessInterface::class)
        ), '(Psl\Collection\CollectionInterface|Iterator)&Psl\Collection\IndexAccessInterface'];
    }
}
