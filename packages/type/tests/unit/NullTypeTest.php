<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

final class NullTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\null();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [null, null];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [Type\bool()];
        yield [1];
        yield [0];
        yield [false];
        yield [true];
        yield [''];
        yield ['null'];
        yield ['foo'];
        yield [[null]];
        yield [[]];
        yield [[1, 2, 3]];
        yield [static::stringable('')];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'null'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\null(), Type\null());
    }
}
