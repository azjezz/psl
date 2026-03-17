<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

final class NonNullTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\nonnull();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [$_ = Type\bool(), $_];
        yield [$_ = 1, $_];
        yield [$_ = 0, $_];
        yield [$_ = false, $_];
        yield [$_ = true, $_];
        yield [$_ = '', $_];
        yield [$_ = 'null', $_];
        yield [$_ = 'foo', $_];
        yield [$_ = [null], $_];
        yield [$_ = [], $_];
        yield [$_ = [1, 2, 3], $_];
        yield [$_ = static::stringable(''), $_];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'nonnull'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\nonnull(), Type\nonnull());
    }
}
