<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Collection\SetInterface;
use Psl\Dict;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

/**
 * @extends TypeTest<SetInterface<array-key>>
 */
final class SetTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\set(Type\int());
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            new Collection\Set([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]),
        ];

        yield [
            Vec\range(1, 10),
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $key): string => (string) $key),
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            new Collection\MutableSet([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        ];

        yield [
            new Collection\MutableVector([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            new Collection\Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
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
        yield [$this->getType(), 'Psl\Collection\SetInterface<int>'];
        yield [Type\set(Type\string()), 'Psl\Collection\SetInterface<string>'];
    }

    /**
     * @param SetInterface<array-key>|mixed $a
     * @param SetInterface<array-key>|mixed $b
     */
    #[\Override]
    protected function equals($a, $b): bool
    {
        if (Type\instance_of(SetInterface::class)->matches($a)) {
            $a = $a->toArray();
        }

        if (Type\instance_of(SetInterface::class)->matches($b)) {
            $b = $b->toArray();
        }

        return parent::equals($a, $b);
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion value' => [
            Type\set(Type\int()),
            new Collection\MutableSet(['foo' => 'nope']),
            'Expected "' . SetInterface::class . '<int>", got "string" at path "nope".',
        ];
        yield 'nested' => [
            Type\set(Type\string()),
            new Collection\MutableSet([1 => 123]),
            'Expected "' . SetInterface::class . '<string>", got "int" at path "123".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion value' => [
            Type\set(Type\int()),
            ['nope' => 'nope'],
            'Could not coerce "string" to type "' . SetInterface::class . '<int>" at path "nope".',
        ];
        yield 'invalid iterator first item' => [
            Type\set(Type\int()),
            (static function (): iterable {
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . SetInterface::class . '<int>" at path "first()".',
        ];
        yield 'invalid iterator second item' => [
            Type\set(Type\int()),
            (static function (): iterable {
                yield 0;
                yield Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "' . SetInterface::class . '<int>" at path "0.next()".',
        ];
        yield 'iterator throwing exception' => [
            Type\set(Type\int()),
            (static function (): iterable {
                yield 0;
                throw new RuntimeException('whoops');
            })(),
            'Could not coerce "null" to type "' . SetInterface::class . '<int>" at path "0.next()": whoops.',
        ];
        yield 'iterator yielding null key' => [
            Type\set(Type\int()),
            (static function (): iterable {
                yield null => 'nope';
            })(),
            'Could not coerce "string" to type "' . SetInterface::class . '<int>" at path "null".',
        ];
        yield 'iterator yielding string key, null value' => [
            Type\set(Type\int()),
            (static function (): iterable {
                yield 'nope' => 'bar';
            })(),
            'Could not coerce "string" to type "' . SetInterface::class . '<int>" at path "nope".',
        ];
        yield 'iterator yielding object key' => [
            Type\set(Type\int()),
            (static function (): iterable {
                yield 'nope' => new class() {
                };
            })(),
            'Could not coerce "class@anonymous" to type "' . SetInterface::class . '<int>" at path "nope".',
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
