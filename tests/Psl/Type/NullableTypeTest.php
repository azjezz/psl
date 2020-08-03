<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Type;

class NullableTypeTest extends TypeTest
{
    public function getType(): Type\Type
    {
        return Type\nullable(Type\string());
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
        yield [null, null];
        yield ['null', 'null'];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1.0];
        yield [1.23];
        yield [[]];
        yield [[1]];
        yield [Type\bool()];
        yield [false];
        yield [true];
        yield [STDIN];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'null|string'];
    }
}
