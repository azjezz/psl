<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Math;
use Psl\Type;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class IntRangeTypeTest extends TypeTestCase
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\int_range(Math\INT64_MIN, Math\INT64_MAX);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield [42, 42];
        yield [-42, -42];
        yield [0, 0];
        yield ['0', 0];
        yield ['42', 42];
        yield ['-42', -42];
        yield [$this->stringable('42'), 42];
        yield [$this->stringable((string) Math\INT64_MAX), Math\INT64_MAX];
        yield [(string) Math\INT64_MAX, Math\INT64_MAX];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield ['000', 0];
        yield [1.0, 1];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [1.23];
        yield ['1.23'];
        yield ['1e123'];
        yield [''];
        yield [[]];
        yield [[123]];
        yield [null];
        yield [false];
        yield [$this->stringable('1.23')];
        yield [$this->stringable('-007')];
        yield ['-007'];
        yield ['9223372036854775808'];
        yield [$this->stringable('9223372036854775808')];
        yield ['-9223372036854775809'];
        yield [$this->stringable('-9223372036854775809')];
        yield ['0xFF'];
    }

    #[\Override]
    public function getToStringExamples(): iterable
    {
        yield [Type\int_range(5, 10), 'int<5, 10>'];
        yield [Type\int_range(PHP_INT_MIN, PHP_INT_MAX), 'int<min, max>'];
        yield [Type\int_range(99, PHP_INT_MAX), 'int<99, max>'];
        yield [Type\int_range(PHP_INT_MIN, 42), 'int<min, 42>'];
    }

    public static function outOfBoundsProvider(): iterable
    {
        yield [123];
        yield ['123'];
        yield [-10];
        yield ['-10'];
    }

    /** @dataProvider outOfBoundsProvider */
    public function testCoerceWithLowerBounds(mixed $value): void
    {
        $type = Type\int_range(0, 100);
        $this->expectException(Type\Exception\CoercionException::class);
        $this->expectExceptionMessage('int<0, 100>');
        $type->coerce($value);
    }

    /** @dataProvider outOfBoundsProvider */
    public function testMatchesWithLowerBounds(mixed $value): void
    {
        $type = Type\int_range(0, 100);
        static::assertFalse($type->matches($value));
    }

    /** @dataProvider outOfBoundsProvider */
    public function testAssertWithLowerBounds(mixed $value): void
    {
        $type = Type\int_range(0, 100);
        $this->expectException(Type\Exception\AssertException::class);
        $type->assert($value);
    }
}
