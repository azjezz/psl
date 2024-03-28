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
 * @extends TypeTest<list<mixed>>
 */
final class VecTypeTest extends TypeTest
{
    public function getValidCoercions(): iterable
    {
        yield [
            [],
            []
        ];

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
            Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
        ];

        yield [
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
            [1, 2, 3, 4, 5]
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
            'vec<Psl\Iter\Iterator>'
        ];
    }

    public function getType(): Type\TypeInterface
    {
        return Type\vec(Type\int());
    }

    public function testInvalidAssertionValueType(): void
    {
        try {
            Type\vec(Type\int())->assert(['nope']);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame(
                'Expected "vec<int>", got "string" at path "0".',
                $e->getMessage()
            );
        }
    }

    public function testInvalidCoercionValueType(): void
    {
        try {
            Type\vec(Type\int())->coerce(['nope']);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertSame(
                'Could not coerce "string" to type "vec<int>" at path "0".',
                $e->getMessage()
            );
        }
    }

    public function testNestedAssertionInvalidKey(): void
    {
        try {
            Type\vec(Type\vec(Type\int()))->assert([
                ['nope'],
            ]);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\AssertException::class));
        } catch (Type\Exception\AssertException $e) {
            static::assertSame(
                'Expected "vec<vec<int>>", got "string" at path "0.0".',
                $e->getMessage()
            );
        }
    }
}
