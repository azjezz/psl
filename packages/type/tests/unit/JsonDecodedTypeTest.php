<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Str;
use Psl\Type;

/**
 * @extends TypeTestCase<array{'name': string, 'age': int}>
 */
final class JsonDecodedTypeTest extends TypeTestCase<array>
{
    #[Override]
    public static function getType(): Type\TypeInterface<array>
    {
        return Type\json_decoded::<array>(Type\shape::<string, string|int>([
            'name' => Type\string(),
            'age' => Type\int(),
        ]));
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [
            '{"name": "Alice", "age": 30}',
            ['name' => 'Alice', 'age' => 30],
        ];
        yield [
            '{"name": "Bob", "age": 25}',
            ['name' => 'Bob', 'age' => 25],
        ];
        // Already-decoded value passes through
        yield [
            ['name' => 'Charlie', 'age' => 40],
            ['name' => 'Charlie', 'age' => 40],
        ];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [1];
        yield [false];
        yield [null];
        yield ['not json'];
        yield ['{"name": "Alice"}']; // missing 'age'
        yield ['{invalid}'];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), "json-decoded<array{'name': string, 'age': int}>"];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'non-string input' => [
            Type\json_decoded::<array>(Type\dict::<string, mixed>(Type\string(), Type\mixed())),
            42,
            'Could not coerce "int" to type "json-decoded<dict<string, mixed>>".',
        ];
        yield 'invalid json' => [
            Type\json_decoded::<array>(Type\dict::<string, mixed>(Type\string(), Type\mixed())),
            '{invalid}',
            'Could not coerce "string" to type "json-decoded<dict<string, mixed>>" at path "coerce_input(string): dict<string, mixed>": Syntax error',
        ];
        yield 'decoded value does not match inner type' => [
            Type\json_decoded::<array>(Type\vec::<int>(Type\int())),
            '{"key": "value"}',
            'Could not coerce "string" to type "json-decoded<vec<int>>" at path "coerce_output(array): vec<int>.key".',
        ];
    }

    #[DataProvider('provideCoerceExceptionExpectations')]
    public function testInvalidCoercionTypeExceptions(
        Type\TypeInterface<mixed> $type,
        mixed $data,
        string $expectedMessage,
    ): void {
        try {
            $type->coerce($data);
            static::fail(Str\format('Expected "%s" exception to be thrown.', Type\Exception\CoercionException::class));
        } catch (Type\Exception\CoercionException $e) {
            static::assertStringContainsString($expectedMessage, $e->getMessage());
        }
    }

    public function testCoercesJsonStringInShape(): void
    {
        $shape = Type\shape::<string, string|array>([
            'name' => Type\string(),
            'metadata' => Type\json_decoded::<array>(Type\shape::<string, string|bool>([
                'role' => Type\string(),
                'active' => Type\bool(),
            ])),
        ]);

        $result = $shape->coerce([
            'name' => 'Alice',
            'metadata' => '{"role": "admin", "active": true}',
        ]);

        static::assertSame(
            [
                'name' => 'Alice',
                'metadata' => ['role' => 'admin', 'active' => true],
            ],
            $result,
        );
    }

    public function testPassesThroughAlreadyDecodedValueInShape(): void
    {
        $shape = Type\shape::<string, string|array>([
            'name' => Type\string(),
            'metadata' => Type\json_decoded::<array>(Type\shape::<string, string>([
                'role' => Type\string(),
            ])),
        ]);

        $result = $shape->coerce([
            'name' => 'Alice',
            'metadata' => ['role' => 'admin'],
        ]);

        static::assertSame(
            [
                'name' => 'Alice',
                'metadata' => ['role' => 'admin'],
            ],
            $result,
        );
    }

    public function testWithSimpleType(): void
    {
        $type = Type\json_decoded::<int>(Type\int());

        static::assertSame(42, $type->coerce('42'));
        static::assertSame(42, $type->coerce(42));
    }
}
