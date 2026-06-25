<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use BcMath\Number;
use Override;
use Psl\Type;

use const STDIN;

/** @extends TypeTestCase<numeric> */
final class NumericTypeTest extends TypeTestCase
{
    /**
     * @return Type\Type<numeric>
     */
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\numeric();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield [1.0, 1.0];
        yield [1.23, 1.23];
        yield [1e23, 1e23];
        yield [0x23, 0x23];
        yield [0b1, 0b1];
        yield [-1, -1];
        yield [-0.123, -0.123];
        yield [+1, 1];
        yield [+0.123, 0.123];
        yield ['123', '123'];
        yield ['0', '0'];
        yield ['1.0', '1.0'];
        yield ['1.23', '1.23'];
        yield ['1e23', '1e23'];
        yield ['-1', '-1'];
        yield ['-0.123', '-0.123'];
        yield ['+1', '+1'];
        yield ['+0.123', '+0.123'];
        yield [static::stringable('123'), '123'];
        yield [new Number('1.23'), '1.23'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [''];
        yield ['hello'];
        yield [static::stringable('hello')];
        yield [[]];
        yield [[1]];
        yield [Type\bool()];
        yield [null];
        yield [false];
        yield [true];
        yield [STDIN];
        yield ['0x23'];
        yield ['0b1'];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'numeric'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\numeric(), Type\numeric());
    }
}
