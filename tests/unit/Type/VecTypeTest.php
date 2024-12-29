<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

/**
 * @extends TypeTest<list<mixed>>
 */
final class VecTypeTest extends TypeTest
{
    public function getValidCoercions(): iterable
    {
        yield [
            [],
            [],
        ];

        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Vector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Map([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Vector(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            new Collection\Map(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            Dict\map_keys(Vec\range(1, 10), static fn(int $key): string => (string) $key),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];

        yield [
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
            [1, 2, 3, 4, 5],
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
        yield [$this->getType(), 'vec<int>'];
        yield [Type\vec(Type\string()), 'vec<string>'];
        yield [
            Type\vec(Type\instance_of(Iter\Iterator::class)),
            'vec<Psl\Iter\Iterator>',
        ];
    }

    public function getType(): Type\TypeInterface
    {
        return Type\vec(Type\int());
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion value' => [
            Type\vec(Type\int()),
            ['nope'],
            'Expected "vec<int>", got "string" at path "0".',
        ];
        yield 'nested' => [
            Type\vec(Type\vec(Type\int())),
            [['nope']],
            'Expected "vec<vec<int>>", got "string" at path "0.0".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion value' => [
            Type\vec(Type\int()),
            ['nope'],
            'Could not coerce "string" to type "vec<int>" at path "0".',
        ];
        yield 'invalid iterator first item' => [
            Type\vec(Type\int()),
            (static function () {
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "vec<int>" at path "first()".',
        ];
        yield 'invalid iterator second item' => [
            Type\vec(Type\int()),
            (static function () {
                yield 0;
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "vec<int>" at path "0.next()".',
        ];
        yield 'iterator throwing exception' => [
            Type\vec(Type\int()),
            (static function () {
                yield 0;
                throw new RuntimeException('whoops');
            })(),
            'Could not coerce "null" to type "vec<int>" at path "0.next()": whoops.',
        ];
        yield 'iterator yielding null key' => [
            Type\vec(Type\int()),
            (static function () {
                yield null => 'nope';
            })(),
            'Could not coerce "string" to type "vec<int>" at path "null".',
        ];
        yield 'iterator yielding object key' => [
            Type\vec(Type\int()),
            (static function () {
                yield new class() {
                } => 'nope';
            })(),
            'Could not coerce "string" to type "vec<int>" at path "class@anonymous".',
        ];
    }

    /**
     * @dataProvider provideAssertExceptionExpectations
     */
    public function testInvalidAssertionTypeExceptions(
        Type\TypeInterface $type,
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

    /**
     * @dataProvider provideCoerceExceptionExpectations
     */
    public function testInvalidCoercionTypeExceptions(
        Type\TypeInterface $type,
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
}
