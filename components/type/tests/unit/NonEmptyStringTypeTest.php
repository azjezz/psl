<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

/**
 * @extends TypeTestCase<non-empty-string>
 */
final class NonEmptyStringTypeTest extends TypeTestCase
{
    /**
     * @return Type\Type<non-empty-string>
     */
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\non_empty_string();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['hello', 'hello'];
        yield [static::stringable('hello'), 'hello'];
        yield [123, '123'];
        yield [0, '0'];
        yield ['0', '0'];
        yield ['123', '123'];
        yield ['1e23', '1e23'];
        yield [static::stringable('123'), '123'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [''];
        yield [1.0];
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
        yield [static::getType(), 'non-empty-string'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\non_empty_string(), Type\non_empty_string());
    }
}
