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
 * @extends TypeTest<non-empty-array<array-key, mixed>>
 */
final class NonEmptyDictTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\non_empty_dict(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
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
            Dict\map_keys(
                Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
                static fn(int $key): string => Str\format('00%d', $key),
            ),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [[]];
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
        yield [$this->getType(), 'non-empty-dict<int, int>'];
        yield [Type\non_empty_dict(Type\array_key(), Type\int()), 'non-empty-dict<array-key, int>'];
        yield [Type\non_empty_dict(Type\array_key(), Type\string()), 'non-empty-dict<array-key, string>'];
        yield [
            Type\non_empty_dict(Type\array_key(), Type\instance_of(Iter\Iterator::class)),
            'non-empty-dict<array-key, Psl\Iter\Iterator>',
        ];
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion key' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            ['nope' => 1],
            'Expected "non-empty-dict<int, int>", got "string" at path "key(nope)".',
        ];
        yield 'invalid assertion value' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            [0 => 'nope'],
            'Expected "non-empty-dict<int, int>", got "string" at path "0".',
        ];
        yield 'nested' => [
            Type\non_empty_dict(Type\int(), Type\non_empty_dict(Type\int(), Type\int())),
            [0 => ['nope' => 'nope']],
            'Expected "non-empty-dict<int, non-empty-dict<int, int>>", got "string" at path "0.key(nope)".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion key' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            ['nope' => 1],
            'Could not coerce "string" to type "non-empty-dict<int, int>" at path "key(nope)".',
        ];
        yield 'invalid coercion value' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            [0 => 'nope'],
            'Could not coerce "string" to type "non-empty-dict<int, int>" at path "0".',
        ];
        yield 'invalid iterator first item' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            (static function () {
                yield 0 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "non-empty-dict<int, int>" at path "first()".',
        ];
        yield 'invalid iterator second item' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            (static function () {
                yield 0 => 0;
                yield 1 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "non-empty-dict<int, int>" at path "0.next()".',
        ];
        yield 'iterator throwing exception' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            (static function () {
                throw new RuntimeException('whoops');
                yield;
            })(),
            'Could not coerce "null" to type "non-empty-dict<int, int>" at path "first()": whoops.',
        ];
        yield 'iterator yielding null key' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            (static function () {
                yield null => 'nope';
            })(),
            'Could not coerce "null" to type "non-empty-dict<int, int>" at path "key(null)".',
        ];
        yield 'iterator yielding object key' => [
            Type\non_empty_dict(Type\int(), Type\int()),
            (static function () {
                yield new class() {
                } => 'nope';
            })(),
            'Could not coerce "class@anonymous" to type "non-empty-dict<int, int>" at path "key(class@anonymous)".',
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
