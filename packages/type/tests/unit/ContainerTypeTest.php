<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

/**
 * @extends TypeTestCase<iterable<int, int>>
 */
final class ContainerTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\container(Type\int(), Type\int());
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ];
        yield [Vec\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
        yield [Vec\range(1, 10), [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];

        yield [
            Dict\map(Vec\range(1, 10), static fn(int $value): string => (string) $value),
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
        yield [static::getType(), 'container<int, int>'];
        yield [Type\container(Type\array_key(), Type\int()), 'container<array-key, int>'];
        yield [Type\container(Type\array_key(), Type\string()), 'container<array-key, string>'];
        yield [
            Type\container(Type\array_key(), Type\instance_of(Iter\Iterator::class)),
            'container<array-key, Psl\Iter\Iterator>',
        ];
    }

    /**
     * @param iterable<int, int> $a
     * @param iterable<int, int> $b
     */
    #[Override]
    protected static function equals(mixed $a, mixed $b): bool
    {
        return $a === $b;
    }

    public static function provideAssertExceptionExpectations(): iterable
    {
        yield 'invalid assertion key' => [
            Type\container(Type\int(), Type\int()),
            ['nope' => 1],
            'Expected "container<int, int>", got "string" at path "key(nope)".',
        ];
        yield 'invalid assertion value' => [
            Type\container(Type\int(), Type\int()),
            [0 => 'nope'],
            'Expected "container<int, int>", got "string" at path "0".',
        ];
        yield 'nested' => [
            Type\container(Type\int(), Type\container(Type\int(), Type\int())),
            [0 => ['nope' => 'nope']],
            'Expected "container<int, container<int, int>>", got "string" at path "0.key(nope)".',
        ];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid coercion key' => [
            Type\container(Type\int(), Type\int()),
            ['nope' => 1],
            'Could not coerce "string" to type "container<int, int>" at path "key(nope)".',
        ];
        yield 'invalid coercion value' => [
            Type\container(Type\int(), Type\int()),
            [0 => 'nope'],
            'Could not coerce "string" to type "container<int, int>" at path "0".',
        ];
        yield 'invalid iterator first item' => [
            Type\container(Type\int(), Type\int()),
            (static function (): iterable {
                yield 0 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "container<int, int>" at path "first()".',
        ];
        yield 'invalid iterator second item' => [
            Type\container(Type\int(), Type\int()),
            (static function (): iterable {
                yield 0 => 0;
                yield 1 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "container<int, int>" at path "0.next()".',
        ];
        yield 'iterator throwing exception' => [
            Type\container(Type\int(), Type\int()),
            (static function (): iterable {
                throw new RuntimeException('whoops');
                yield;
            })(),
            'Could not coerce "null" to type "container<int, int>" at path "first()": whoops.',
        ];
        yield 'iterator yielding null key' => [
            Type\container(Type\int(), Type\int()),
            (static function (): iterable {
                yield null => 'nope';
            })(),
            'Could not coerce "null" to type "container<int, int>" at path "key(null)".',
        ];
        yield 'iterator yielding object key' => [
            Type\container(Type\int(), Type\int()),
            (static function (): iterable {
                yield new class() {} => 'nope';
            })(),
            'Could not coerce "class@anonymous" to type "container<int, int>" at path "key(class@anonymous)".',
        ];
    }

    #[DataProvider('provideAssertExceptionExpectations')]
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

    #[DataProvider('provideCoerceExceptionExpectations')]
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
