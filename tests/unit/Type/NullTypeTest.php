<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Type;

final class NullTypeTest extends TypeTestCase
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\null();
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield [null, null];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
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
        yield [$this->stringable('')];
    }

    #[\Override]
    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'null'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\null(), Type\null());
    }
}
