<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

/**
 * @extends TypeTest<array<array-key, mixed>>
 */
final class DictTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\dict(Type\int(), Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
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
            Dict\map_keys(Vec\range(1, 10), static fn(int $key): string => (string)$key),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Dict\map(
                Vec\range(1, 10),
                static fn(int $value): string => Str\format('00%d', $value)
            ),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            Dict\map_keys(
                Dict\map(
                    Vec\range(1, 10),
                    static fn(int $value): string => Str\format('00%d', $value)
                ),
                static fn(int $key): string => Str\format('00%d', $key)
            ),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
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
        yield [$this->getType(), 'dict<int, int>'];
        yield [Type\dict(Type\array_key(), Type\int()), 'dict<array-key, int>'];
        yield [Type\dict(Type\array_key(), Type\string()), 'dict<array-key, string>'];
        yield [
            Type\dict(Type\array_key(), Type\instance_of(Iter\Iterator::class)),
            'dict<array-key, Psl\Iter\Iterator>'
        ];
    }

    public function testInvalidAssertionKeyType(): void
    {
        try {
            Type\dict(Type\int(), Type\int())->assert([
                'nope' => 1,
            ]);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame(
                'Expected "dict<int, int>", got "string" at path "key(nope)".',
                $e->getMessage()
            );
        }
    }

    public function testInvalidAssertionValueType(): void
    {
        try {
            Type\dict(Type\int(), Type\int())->assert([
                0 => 'nope',
            ]);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame(
                'Expected "dict<int, int>", got "string" at path "0".',
                $e->getMessage()
            );
        }
    }

    public function testInvalidCoercionKeyType(): void
    {
        try {
            Type\dict(Type\int(), Type\int())->coerce([
                'nope' => 1,
            ]);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame(
                'Could not coerce "string" to type "dict<int, int>" at path "key(nope)".',
                $e->getMessage()
            );
        }
    }

    public function testInvalidCoercionValueType(): void
    {
        try {
            Type\dict(Type\int(), Type\int())->coerce([
                0 => 'nope',
            ]);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame(
                'Could not coerce "string" to type "dict<int, int>" at path "0".',
                $e->getMessage()
            );
        }
    }

    public function testNestedAssertionInvalidKey(): void
    {
        try {
            Type\dict(Type\int(), Type\dict(Type\int(), Type\int()))->assert([
                0 => ['nope' => 'nope'],
            ]);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame(
                'Expected "dict<int, dict<int, int>>", got "string" at path "0.key(nope)".',
                $e->getMessage()
            );
        }
    }
}
