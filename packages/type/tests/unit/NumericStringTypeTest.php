<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

/**
 * @extends TypeTestCase<numeric-string>
 */
final class NumericStringTypeTest extends TypeTestCase
{
    /**
     * @return Type\Type<numeric-string>
     */
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\numeric_string();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [123, '123'];
        yield [0, '0'];
        yield [1.0, '1'];
        yield [1.23, '1.23'];
        yield ['0', '0'];
        yield ['123', '123'];
        yield ['1e23', '1e23'];
        yield [static::stringable('123'), '123'];
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
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'numeric-string'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\numeric_string(), Type\numeric_string());
    }
}
