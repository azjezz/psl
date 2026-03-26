<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

final class BoolTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\bool();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [false, false];
        yield [0, false];
        yield ['0', false];
        yield ['false', false];
        yield ['False', false];
        yield ['FALSE', false];
        yield [true, true];
        yield [1, true];
        yield ['1', true];
        yield ['true', true];
        yield ['True', true];
        yield ['TRUE', true];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [1.2];
        yield [Type\bool()];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'bool'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\bool(), Type\bool());
    }
}
