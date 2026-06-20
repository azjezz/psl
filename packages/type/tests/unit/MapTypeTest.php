<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Collection;
use Psl\Collection\MapInterface;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

final class MapTypeTest extends TypeTestCase<Collection\MapInterface<string|int, mixed>>
{
    #[Override]
    public static function getType(): Type\TypeInterface<Collection\MapInterface<string|int, mixed>>
    {
        return Type\map::<int, int>(Type\int(), Type\int());
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            new Collection\Map::<int, int>([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            Vec\range::<int>(1, 10),
            new Collection\Map::<int, int>([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            Vec\range::<int>(1, 10),
            new Collection\Map::<int, int>([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            Dict\map::<int, int, string>(Vec\range::<int>(1, 10), static fn(int $value): string => (string) $value),
            new Collection\Map::<int, int>([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            Dict\map_keys::<int, string, int>(Vec\range::<int>(1, 10), static fn(int $key): string => (string) $key),
            new Collection\Map::<int, int>([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            Dict\map::<int, int, string>(Vec\range::<int>(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            new Collection\Map::<int, int>([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [1.0];
        yield [1.23];
        yield [Type\bool()];
        yield [null];
        yield [false];
        yield [true];
        yield [STDIN];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'Psl\Collection\MapInterface<int, int>'];
        yield [Type\map::<string|int, int>(Type\array_key(), Type\int()), 'Psl\Collection\MapInterface<array-key, int>'];
        yield [Type\map::<string|int, string>(Type\array_key(), Type\string()), 'Psl\Collection\MapInterface<array-key, string>'];
        yield [
            Type\map::<string|int, Iter\Iterator>(Type\array_key(), Type\instance_of::<Iter\Iterator>(Iter\Iterator::class)),
            'Psl\Collection\MapInterface<array-key, Psl\Iter\Iterator>',
        ];
    }

    /**
     * @param MapInterface<array-key, mixed>|mixed $a
     * @param MapInterface<array-key, mixed>|mixed $b
     */
    #[Override]
    protected static function equals(mixed $a, mixed $b): bool
    {
        if (Type\instance_of::<MapInterface>(MapInterface::class)->matches($a)) {
            $a = $a->toArray();
        }

        if (Type\instance_of::<MapInterface>(MapInterface::class)->matches($b)) {
            $b = $b->toArray();
        }

        return parent::equals($a, $b);
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion key' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            new Collection\Map::<string, int>(['nope' => 1]),
            'Expected "' . MapInterface::class . '<int, int>", got "string" at path "key(nope)".',
        ];
        yield 'invalid assertion value' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            new Collection\Map::<int, string>([0 => 'nope']),
            'Expected "' . MapInterface::class . '<int, int>", got "string" at path "0".',
        ];
        yield 'nested' => [
            Type\map::<int, MapInterface<int, int>>(Type\int(), Type\map::<int, int>(Type\int(), Type\int())),
            new Collection\Map::<int, Collection\Map>([0 => new Collection\Map::<string, string>(['nope' => 'nope'])]),
            'Expected "'
                . MapInterface::class
                . '<int, '
                . MapInterface::class
                . '<int, int>>", got "string" at path "0.key(nope)".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion key' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            ['nope' => 1],
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "key(nope)".',
        ];
        yield 'invalid coercion value' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            [0 => 'nope'],
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "0".',
        ];
        yield 'invalid iterator first item' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            (static function (): iterable {
                yield 0 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "first()".',
        ];
        yield 'invalid iterator second item' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            (static function (): iterable {
                yield 0 => 0;
                yield 1 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "0.next()".',
        ];
        yield 'iterator throwing exception' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            (static function (): iterable {
                throw new RuntimeException('whoops');
                yield;
            })(),
            'Could not coerce "null" to type "' . MapInterface::class . '<int, int>" at path "first()": whoops.',
        ];
        yield 'iterator yielding null key' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            (static function (): iterable {
                yield null => 'nope';
            })(),
            'Could not coerce "null" to type "' . MapInterface::class . '<int, int>" at path "key(null)".',
        ];
        yield 'iterator yielding object key' => [
            Type\map::<int, int>(Type\int(), Type\int()),
            (static function (): iterable {
                yield new class() {} => 'nope';
            })(),
            'Could not coerce "class@anonymous" to type "'
                . MapInterface::class
                . '<int, int>" at path "key(class@anonymous)".',
        ];
    }

    #[DataProvider('provideAssertExceptionExpectations')]
    public function testInvalidAssertionTypeExceptions(
        Type\TypeInterface<mixed> $type,
        mixed $data,
        string $expectedMessage,
    ): void {
        try {
            $type->assert($data);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame($expectedMessage, $e->getMessage());
        }
    }

    #[DataProvider('provideCoerceExceptionExpectations')]
    public function testInvalidCoercionTypeExceptions(
        Type\TypeInterface<mixed> $type,
        mixed $data,
        string $expectedMessage,
    ): void {
        try {
            $type->coerce($data);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testMatchesReturnsFalseForInvalidValueType(): void
    {
        $type = Type\map::<int, int>(Type\int(), Type\int());
        $map = new Collection\Map::<int, string>([0 => 'not an int']);

        static::assertFalse($type->matches($map));
    }

    public function testMatchesReturnsFalseForInvalidKeyType(): void
    {
        $type = Type\map::<int, string>(Type\int(), Type\string());
        $map = new Collection\Map::<string, string>(['not_int' => 'value']);

        static::assertFalse($type->matches($map));
    }
}
