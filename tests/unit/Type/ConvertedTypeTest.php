<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use DateTimeImmutable;
use Psl\Str;
use Psl\Type;
use RuntimeException;

final class ConvertedTypeTest extends TypeTest
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function getType(): Type\TypeInterface
    {
        return Type\converted(
            Type\string(),
            Type\instance_of(DateTimeImmutable::class),
            static fn(string $value): DateTimeImmutable => DateTimeImmutable::createFromFormat(
                self::DATE_FORMAT,
                $value,
            ) ?: throw new RuntimeException('Unable to parse date format'),
        );
    }

    public function getValidCoercions(): iterable
    {
        yield ['2023-04-27 08:28:00', DateTimeImmutable::createFromFormat(self::DATE_FORMAT, '2023-04-27 08:28:00')];
        yield [
            $this->stringable('2023-04-27 08:28:00'),
            DateTimeImmutable::createFromFormat(self::DATE_FORMAT, '2023-04-27 08:28:00'),
        ];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1];
        yield [false];
        yield [''];
        yield ['2023-04-27'];
        yield ['2023-04-27 08:26'];
        yield ['27/04/2023'];
        yield [$this->stringable('2023-04-27')];
    }

    /**
     * @param DateTimeImmutable|mixed $a
     * @param DateTimeImmutable|mixed $b
     */
    protected function equals($a, $b): bool
    {
        if (Type\instance_of(DateTimeImmutable::class)->matches($a)) {
            $a = $a->format(self::DATE_FORMAT);
        }

        if (Type\instance_of(DateTimeImmutable::class)->matches($b)) {
            $b = $b->format(self::DATE_FORMAT);
        }

        return parent::equals($a, $b);
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), DateTimeImmutable::class];
    }

    public static function provideCoerceExceptionExpectations(): iterable
    {
        yield 'Coerce input error' => [
            Type\converted(Type\int(), Type\string(), static fn(int $i): string => (string) $i),
            new class() {
            },
            'Could not coerce "class@anonymous" to type "int" at path "coerce_input(class@anonymous): int".',
        ];
        yield 'Convert exception error' => [
            Type\converted(
                Type\int(),
                Type\string(),
                static fn(int $_i): string => throw new RuntimeException('not possible'),
            ),
            1,
            'Could not coerce "int" to type "string" at path "convert(int): string": not possible.',
        ];
        yield 'Coerce output error' => [
            Type\converted(
                Type\int(),
                Type\string(),
                static fn(int $_i): object => new class() {
                },
            ),
            1,
            'Could not coerce "class@anonymous" to type "string" at path "coerce_output(class@anonymous): string".',
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
