<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Type;

/**
 * @extends TypeTestCase<non-empty-string>
 */
final class NonEmptyStringTypeTest extends TypeTestCase
{
    /**
     * @return Type\Type<non-empty-string>
     */
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\non_empty_string();
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['hello', 'hello'];
        yield [$this->stringable('hello'), 'hello'];
        yield [123, '123'];
        yield [0, '0'];
        yield ['0', '0'];
        yield ['123', '123'];
        yield ['1e23', '1e23'];
        yield [$this->stringable('123'), '123'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
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

    #[\Override]
    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'non-empty-string'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\non_empty_string(), Type\non_empty_string());
    }
}
