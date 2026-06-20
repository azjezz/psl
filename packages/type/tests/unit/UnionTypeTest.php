<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Collection\CollectionInterface;
use Psl\Collection\IndexAccessInterface;
use Psl\Type;

final class UnionTypeTest extends TypeTestCase<int|bool>
{
    #[Override]
    public static function getType(): Type\TypeInterface<int|bool>
    {
        return Type\union::<int|bool>(Type\int(), Type\bool());
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [1, 1];
        yield ['1', 1];
        yield ['123', 123];
        yield [true, true];
        yield [false, false];
        yield [static::stringable('123'), 123];
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
        yield [Type\union::<bool|string>(Type\bool(), Type\string()), 'bool|string'];
        yield [Type\union::<bool|float>(Type\bool(), Type\float()), 'bool|float'];
        yield [Type\union::<bool|float|int>(Type\bool(), Type\float(), Type\int()), 'bool|float|int'];
        yield [Type\union::<bool|int|float>(Type\bool(), Type\num()), 'bool|num'];
        yield [Type\union::<bool|string|int>(Type\bool(), Type\array_key()), 'bool|array-key'];
        yield [
            Type\union::<bool|(IndexAccessInterface&CollectionInterface)>(Type\bool(), Type\intersection::<IndexAccessInterface, CollectionInterface>(
                Type\instance_of::<IndexAccessInterface>(IndexAccessInterface::class),
                Type\instance_of::<CollectionInterface>(CollectionInterface::class),
            )),
            'bool|(Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface)',
        ];
        yield [
            Type\union::<(IndexAccessInterface&CollectionInterface)|bool|string>(
                Type\intersection::<IndexAccessInterface, CollectionInterface>(
                    Type\instance_of::<IndexAccessInterface>(IndexAccessInterface::class),
                    Type\instance_of::<CollectionInterface>(CollectionInterface::class),
                ),
                Type\bool(),
                Type\non_empty_string(),
            ),
            '((Psl\Collection\IndexAccessInterface&Psl\Collection\CollectionInterface)|bool)|non-empty-string',
        ];
        yield [
            Type\union::<null|array|string>(
                Type\null(),
                Type\vec::<int>(Type\positive_int()),
                Type\literal_scalar::<string>('php'),
                Type\literal_scalar::<string>('still'),
                Type\literal_scalar::<string>('alive'),
            ),
            'null|vec<positive-int>|"php"|"still"|"alive"',
        ];
    }

    public function testLiteralUnions(): void
    {
        $type = Type\union::<string>(
            Type\literal_scalar::<string>('a'),
            Type\literal_scalar::<string>('b'),
            Type\literal_scalar::<string>('c'),
            Type\literal_scalar::<string>('d'),
        );

        foreach (['a', 'b', 'c', 'd'] as $item) {
            static::assertTrue($type->matches($item));
            static::assertSame($item, $type->assert($item));
        }

        static::assertFalse($type->matches('e'));
    }
}
