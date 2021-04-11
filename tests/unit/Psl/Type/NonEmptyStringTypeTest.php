<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Type;

/**
 * @extends TypeTest<non-empty-string>
 */
final class NonEmptyStringTypeTest extends TypeTest
{
    /**
     * @return Type\Type<non-empty-string>
     */
    public function getType(): Type\TypeInterface
    {
        return Type\non_empty_string();
    }

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

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'non-empty-string'];
    }
}
