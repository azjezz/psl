<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Str;
use Psl\Type;
use RuntimeException;

final class ConvertedTypeTest extends TypeTestCase
{
    private const string DATE_FORMAT = 'Y-m-d H:i:s';

    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\converted(
            Type\string(),
            Type\instance_of(DateTimeImmutable::class),
            static fn(string $value): DateTimeImmutable => ($dt = DateTimeImmutable::createFromFormat(
                self::DATE_FORMAT,
                $value,
            ))
                    ? $dt
                    : throw new RuntimeException('Unable to parse date format'),
        );
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['2023-04-27 08:28:00', DateTimeImmutable::createFromFormat(self::DATE_FORMAT, '2023-04-27 08:28:00')];
        yield [
            static::stringable('2023-04-27 08:28:00'),
            DateTimeImmutable::createFromFormat(self::DATE_FORMAT, '2023-04-27 08:28:00'),
        ];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [1];
        yield [false];
        yield [''];
        yield ['2023-04-27'];
        yield ['2023-04-27 08:26'];
        yield ['27/04/2023'];
        yield [static::stringable('2023-04-27')];
    }

    /**
     * @param DateTimeImmutable|mixed $a
     * @param DateTimeImmutable|mixed $b
     */
    #[Override]
    protected static function equals(mixed $a, mixed $b): bool
    {
        if (Type\instance_of(DateTimeImmutable::class)->matches($a)) {
            $a = $a->format(self::DATE_FORMAT);
        }

        if (Type\instance_of(DateTimeImmutable::class)->matches($b)) {
            $b = $b->format(self::DATE_FORMAT);
        }

        return parent::equals($a, $b);
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), DateTimeImmutable::class];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'Coerce input error' => [
            Type\converted(Type\int(), Type\string(), static fn(int $i): string => (string) $i),
            new class() {},
            'Could not coerce "class@anonymous" to type "int" at path "coerce_input(class@anonymous): int".',
        ];
        yield 'Convert exception error' => [
            Type\converted(
                Type\int(),
                Type\string(),
                static fn(int $_): string => throw new RuntimeException('not possible'),
            ),
            1,
            'Could not coerce "int" to type "string" at path "convert(int): string": not possible.',
        ];
        yield 'Coerce output error' => [
            Type\converted(Type\int(), Type\string(), static fn(int $_): object => new class() {}),
            1,
            'Could not coerce "class@anonymous" to type "string" at path "coerce_output(class@anonymous): string".',
        ];
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
