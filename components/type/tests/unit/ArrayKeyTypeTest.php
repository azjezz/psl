<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

final class ArrayKeyTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\array_key();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield ['0', '0'];
        yield ['123', '123'];
        yield ['1e23', '1e23'];
        yield [static::stringable('123'), '123'];
        yield [1.0, 1];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [1.23];
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
        yield [static::getType(), 'array-key'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\array_key(), Type\array_key());
    }
}
