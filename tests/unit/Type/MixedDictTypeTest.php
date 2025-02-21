<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Generator;
use Psl\Collection;
use Psl\Dict;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;
use SplObjectStorage;
use stdClass;

final class MixedDictTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\mixed_dict();
    }

    public function getValidCoercions(): iterable
    {
        yield [
            [],
            [],
        ];

        yield [
            ['foo' => 'bar'],
            ['foo' => 'bar'],
        ];

        $object = new stdClass();
        yield [[0, 1, 2, 'foo' => 'bar', [], $object], [0, 1, 2, 'foo' => 'bar', [], $object]];

        $gen = $this->generator();
        yield [$gen, [1, 2, 'asdf' => 'key']];

        yield [
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
            Dict\map(Vec\range(1, 10), static fn(int $value): string => Str\format('00%d', $value)),
            ['001', '002', '003', '004', '005', '006', '007', '008', '009', '0010'],
        ];

        $spl = new SplObjectStorage();
        $spl[$object] = 'test';
        yield [$spl, [$object]];
    }

    private function generator(): Generator
    {
        yield 1;
        yield 2;
        yield 'asdf' => 'key';
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1];
        yield [new stdClass()];
        yield ['asdf'];
        yield [false];
        yield [null];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'dict<array-key, mixed>'];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'invalid iterator first item' => [
            Type\mixed_dict(),
            (static function (): iterable {
                yield 0 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "dict<array-key, mixed>" at path "first()".',
        ];
        yield 'invalid iterator second item' => [
            Type\mixed_dict(),
            (static function (): iterable {
                yield 0 => 0;
                yield 1 => Type\int()->coerce('nope');
            })(),
            'Could not coerce "string" to type "dict<array-key, mixed>" at path "0.next()".',
        ];
        yield 'iterator throwing exception' => [
            Type\mixed_dict(),
            (static function (): iterable {
                throw new RuntimeException('whoops');
                yield;
            })(),
            'Could not coerce "null" to type "dict<array-key, mixed>" at path "first()": whoops.',
        ];
        yield 'iterator yielding null key' => [
            Type\mixed_dict(),
            (static function (): iterable {
                yield null => 'nope';
            })(),
            'Could not coerce "null" to type "dict<array-key, mixed>" at path "key(null)".',
        ];
        yield 'iterator yielding object key' => [
            Type\mixed_dict(),
            (static function (): iterable {
                yield new class() {
                } => 'nope';
            })(),
            'Could not coerce "class@anonymous" to type "dict<array-key, mixed>" at path "key(class@anonymous)".',
        ];
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
