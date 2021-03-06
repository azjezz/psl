<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Collection\CollectionInterface;
use Psl\Collection\IndexAccessInterface;
use Psl\Type;

final class UnionTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\union(Type\int(), Type\bool());
    }

    public function getValidCoercions(): iterable
    {
        yield [1, 1];
        yield ['1', 1];
        yield ['123', 123];
        yield [true, true];
        yield [false, false];
        yield [$this->stringable('123'), 123];
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
        yield [Type\union(Type\bool(), Type\string()), 'bool|string'];
        yield [Type\union(Type\bool(), Type\float()), 'bool|float'];
        yield [Type\union(Type\bool(), Type\union(Type\float(), Type\int())), 'bool|float|int'];
        yield [Type\union(Type\bool(), Type\num()), 'bool|num'];
        yield [Type\union(Type\bool(), Type\array_key()), 'bool|array-key'];
        yield [
            Type\union(
                Type\bool(),
                Type\intersection(
                    Type\object(IndexAccessInterface::class),
                    Type\object(CollectionInterface::class)
                )
            ),
            'bool|(Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface)'
        ];
        yield [
            Type\union(
                Type\intersection(
                    Type\object(IndexAccessInterface::class),
                    Type\object(CollectionInterface::class)
                ),
                Type\bool()
            ),
            '(Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface)|bool'
        ];
    }
}
