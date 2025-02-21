<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Collection\MutableSetInterface;
use Psl\Dict;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

/**
 * @extends TypeTest<MutableSetInterface<array-key>>
 */
final class MutableSetTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\mutable_set(Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            new Collection\MutableSet([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]),
        ];

        yield [
            Vec\range(1, 10),
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $key): string => (string) $key),
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            new Collection\Vector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
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
        yield [$this->getType(), 'Psl\Collection\MutableSetInterface<int>'];
        yield [Type\mutable_set(Type\string()), 'Psl\Collection\MutableSetInterface<string>'];
    }

    /**
     * @param MutableSetInterface<array-key>|mixed $a
     * @param MutableSetInterface<array-key>|mixed $b
     */
    #[\Override]
    protected function equals($a, $b): bool
    {
        if (Type\instance_of(MutableSetInterface::class)->matches($a)) {
            $a = $a->toArray();
        }

        if (Type\instance_of(MutableSetInterface::class)->matches($b)) {
            $b = $b->toArray();
        }

        return parent::equals($a, $b);
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion value' => [
            Type\mutable_set(Type\int()),
            new Collection\MutableSet(['nope' => 'nope']),
            'Expected "' . MutableSetInterface::class . '<int>", got "string" at path "nope".',
        ];
        yield 'nested' => [
            Type\mutable_set(Type\string()),
            new Collection\MutableSet([123 => 123]),
            'Expected "' . MutableSetInterface::class . '<string>", got "int" at path "123".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion value' => [
            Type\mutable_set(Type\int()),
            ['nope' => 'nope'],
            'Could not coerce "string" to type "' . MutableSetInterface::class . '<int>" at path "nope".',
        ];
        yield 'invalid iterator first item' => [
            Type\mutable_set(Type\int()),
            (static function (): iterable {
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MutableSetInterface::class . '<int>" at path "first()".',
        ];
        yield 'invalid iterator second item' => [
            Type\mutable_set(Type\int()),
            (static function (): iterable {
                yield 0;
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . MutableSetInterface::class . '<int>" at path "0.next()".',
        ];
        yield 'iterator throwing exception' => [
            Type\mutable_set(Type\int()),
            (static function (): iterable {
                yield 0;
                throw new RuntimeException('whoops');
            })(),
            'Could not coerce "null" to type "' . MutableSetInterface::class . '<int>" at path "0.next()": whoops.',
        ];
        yield 'iterator yielding null key' => [
            Type\mutable_set(Type\int()),
            (static function (): iterable {
                yield null => 'nope';
            })(),
            'Could not coerce "string" to type "' . MutableSetInterface::class . '<int>" at path "null".',
        ];
        yield 'iterator yielding string key, null value' => [
            Type\mutable_set(Type\int()),
            (static function (): iterable {
                yield 'nope' => null;
            })(),
            'Could not coerce "null" to type "' . MutableSetInterface::class . '<int>" at path "nope".',
        ];
        yield 'iterator yielding object key' => [
            Type\mutable_set(Type\int()),
            (static function (): iterable {
                yield 'nope' => new class() {
                };
            })(),
            'Could not coerce "class@anonymous" to type "' . MutableSetInterface::class . '<int>" at path "nope".',
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
