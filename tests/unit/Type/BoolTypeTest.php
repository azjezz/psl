<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Type;

final class BoolTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\bool();
    }

    public function getValidCoercions(): iterable
    {
        yield [false, false];
        yield [0, false];
        yield ['0', false];
        yield [true, true];
        yield [1, true];
        yield ['1', true];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield ['true'];
        yield ['false'];
        yield [1.2];
        yield [Type\bool()];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'bool'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\bool(), Type\bool());
    }
}
