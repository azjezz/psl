<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Collection\MutableVectorInterface;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

/**
 * @extends TypeTest<MutableVectorInterface<mixed>>
 */
final class MutableVectorTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\mutable_vector(Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Vec\range(1, 10),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Vec\range(1, 10),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => (string)$value),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map_keys(Vec\range(1, 10), static fn(int $key): string => (string)$key),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
        ];

        yield [
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
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
        yield [$this->getType(), 'Psl\Collection\MutableVectorInterface<int>'];
        yield [Type\mutable_vector(Type\string()), 'Psl\Collection\MutableVectorInterface<string>'];
        yield [
            Type\mutable_vector(Type\instance_of(Iter\Iterator::class)),
            'Psl\Collection\MutableVectorInterface<Psl\Iter\Iterator>'
        ];
    }

    /**
     * @param MutableVectorInterface<mixed>|mixed $a
     * @param MutableVectorInterface<mixed>|mixed $b
     */
    protected function equals($a, $b): bool
    {
        if (Type\instance_of(MutableVectorInterface::class)->matches($a)) {
            $a = $a->toArray();
        }

        if (Type\instance_of(MutableVectorInterface::class)->matches($b)) {
            $b = $b->toArray();
        }

        return parent::equals($a, $b);
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion value' => [
            Type\mutable_vector(Type\int()),
            new Collection\MutableVector(['nope']),
            'Expected "' . MutableVectorInterface::class . '<int>", got "string" at path "0".'
        ];
        yield 'nested' => [
            Type\mutable_vector(Type\mutable_vector(Type\int())),
            new Collection\MutableVector([new Collection\MutableVector(['nope'])]),
            'Expected "' . MutableVectorInterface::class . '<' . MutableVectorInterface::class . '<int>>", got "string" at path "0.0".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion value' => [
            Type\mutable_vector(Type\int()),
            ['nope'],
            'Could not coerce "string" to type "' . MutableVectorInterface::class . '<int>" at path "0".'
        ];
        yield 'invalid iterator first item' => [
            Type\mutable_vector(Type\int()),
            (static function () {
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MutableVectorInterface::class . '<int>" at path "first()".'
        ];
        yield 'invalid iterator second item' => [
            Type\mutable_vector(Type\int()),
            (static function () {
                yield 0;
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MutableVectorInterface::class . '<int>" at path "0.next()".'
        ];
        yield 'iterator throwing exception' => [
            Type\mutable_vector(Type\int()),
            (static function () {
                yield 0;
                throw new RuntimeException('whoops');
            })(),
            'Could not coerce "null" to type "' . MutableVectorInterface::class . '<int>" at path "0.next()": whoops.'
        ];
        yield 'iterator yielding null key' => [
            Type\mutable_vector(Type\int()),
            (static function () {
                yield null => 'nope';
            })(),
            'Could not coerce "string" to type "' . MutableVectorInterface::class . '<int>" at path "null".'
        ];
        yield 'iterator yielding object key' => [
            Type\mutable_vector(Type\int()),
            (static function () {
                yield (new class () {
                }) => 'nope';
            })(),
            'Could not coerce "string" to type "' . MutableVectorInterface::class . '<int>" at path "class@anonymous".'
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
