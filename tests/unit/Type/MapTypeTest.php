<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Collection\MapInterface;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

/**
 * @extends TypeTest<Collection\MapInterface<array-key, mixed>>
 */
final class MapTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\map(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Vec\range(1, 10),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Vec\range(1, 10),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => (string)$value),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map_keys(Vec\range(1, 10), static fn(int $key): string => (string)$key),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1.0];
        yield [1.23];
        yield [Type\bool()];
        yield [null];
        yield [false];
        yield [true];
        yield [STDIN];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'Psl\Collection\MapInterface<int, int>'];
        yield [Type\map(Type\array_key(), Type\int()), 'Psl\Collection\MapInterface<array-key, int>'];
        yield [Type\map(Type\array_key(), Type\string()), 'Psl\Collection\MapInterface<array-key, string>'];
        yield [
            Type\map(Type\array_key(), Type\instance_of(Iter\Iterator::class)),
            'Psl\Collection\MapInterface<array-key, Psl\Iter\Iterator>'
        ];
    }

    /**
     * @param MapInterface<array-key, mixed>|mixed $a
     * @param MapInterface<array-key, mixed>|mixed $b
     */
    protected function equals($a, $b): bool
    {
        if (Type\instance_of(MapInterface::class)->matches($a)) {
            $a = $a->toArray();
        }

        if (Type\instance_of(MapInterface::class)->matches($b)) {
            $b = $b->toArray();
        }

        return parent::equals($a, $b);
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion key' => [
            Type\map(Type\int(), Type\int()),
            new Collection\Map(['nope' => 1]),
            'Expected "' . MapInterface::class . '<int, int>", got "string" at path "key(nope)".'
        ];
        yield 'invalid assertion value' => [
            Type\map(Type\int(), Type\int()),
            new Collection\Map([0 => 'nope']),
            'Expected "' . MapInterface::class . '<int, int>", got "string" at path "0".'
        ];
        yield 'nested' => [
            Type\map(Type\int(), Type\map(Type\int(), Type\int())),
            new Collection\Map([0 => new Collection\Map(['nope' => 'nope'])]),
            'Expected "' . MapInterface::class . '<int, ' . MapInterface::class . '<int, int>>", got "string" at path "0.key(nope)".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion key' => [
            Type\map(Type\int(), Type\int()),
            ['nope' => 1],
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "key(nope)".'
        ];
        yield 'invalid coercion value' => [
            Type\map(Type\int(), Type\int()),
            [0 => 'nope'],
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "0".'
        ];
        yield 'invalid iterator first item' => [
            Type\map(Type\int(), Type\int()),
            (static function () {
                yield 0 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "first()".'
        ];
        yield 'invalid iterator second item' => [
            Type\map(Type\int(), Type\int()),
            (static function () {
                yield 0 => 0;
                yield 1 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MapInterface::class . '<int, int>" at path "0.next()".'
        ];
        yield 'iterator throwing exception' => [
            Type\map(Type\int(), Type\int()),
            (static function () {
                throw new RuntimeException('whoops');
                yield;
            })(),
            'Could not coerce "null" to type "' . MapInterface::class . '<int, int>" at path "first()": whoops.'
        ];
        yield 'iterator yielding null key' => [
            Type\map(Type\int(), Type\int()),
            (static function () {
                yield null => 'nope';
            })(),
            'Could not coerce "null" to type "' . MapInterface::class . '<int, int>" at path "key(null)".'
        ];
        yield 'iterator yielding object key' => [
            Type\map(Type\int(), Type\int()),
            (static function () {
                yield (new class () {
                }) => 'nope';
            })(),
            'Could not coerce "class@anonymous" to type "' . MapInterface::class . '<int, int>" at path "key(class@anonymous)".'
        ];
    }

    /**
     * @dataProvider provideAssertExceptionExpectations
     */
    public function testInvalidAssertionTypeExceptions(Type\TypeInterface $type, mixed $data, string $expectedMessage): void
    {
        try {
            $type->assert($data);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame($expectedMessage, $e->getMessage());
        }
    }

    /**
     * @dataProvider provideCoerceExceptionExpectations
     */
    public function testInvalidCoercionTypeExceptions(Type\TypeInterface $type, mixed $data, string $expectedMessage): void
    {
        try {
            $type->coerce($data);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame($expectedMessage, $e->getMessage());
        }
    }
}
