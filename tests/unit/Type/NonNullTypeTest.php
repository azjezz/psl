<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Type;

final class NonNullTypeTest extends TypeTest
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\nonnull();
    }

    #[\Override]
    public function getValidCoercions(): iterable
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
        yield [$_ = $this->stringable(''), $_];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
    }

    #[\Override]
    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'nonnull'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\nonnull(), Type\nonnull());
    }
}
