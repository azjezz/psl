<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Type;

final class NullTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\null();
    }

    public function getValidCoercions(): iterable
    {
        yield [null, null];
    }

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

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'null'];
    }
}
