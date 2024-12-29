<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

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
        yield [Type\union(Type\bool(), Type\float(), Type\int()), 'bool|float|int'];
        yield [Type\union(Type\bool(), Type\num()), 'bool|num'];
        yield [Type\union(Type\bool(), Type\array_key()), 'bool|array-key'];
        yield [
            Type\union(
                Type\bool(),
                Type\intersection(
                    Type\instance_of(IndexAccessInterface::class),
                    Type\instance_of(CollectionInterface::class),
                ),
            ),
            'bool|(Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface)',
        ];
        yield [
            Type\union(
                Type\intersection(
                    Type\instance_of(IndexAccessInterface::class),
                    Type\instance_of(CollectionInterface::class),
                ),
                Type\bool(),
                Type\non_empty_string(),
            ),
            '((Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface)|bool)|non-empty-string',
        ];
        yield [
            Type\union(
                Type\null(),
                Type\vec(Type\positive_int()),
                Type\literal_scalar('php'),
                Type\literal_scalar('still'),
                Type\literal_scalar('alive'),
            ),
            'null|vec<positive-int>|"php"|"still"|"alive"',
        ];
    }

    public function testLiteralUnions(): void
    {
        $type = Type\union(
            Type\literal_scalar('a'),
            Type\literal_scalar('b'),
            Type\literal_scalar('c'),
            Type\literal_scalar('d'),
        );

        foreach (['a', 'b', 'c', 'd'] as $item) {
            static::assertTrue($type->matches($item));
            static::assertSame($item, $type->assert($item));
        }

        static::assertFalse($type->matches('e'));
    }
}
