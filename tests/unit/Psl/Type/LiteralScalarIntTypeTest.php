<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Type;

final class LiteralScalarIntTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\literal_scalar(5);
    }

    public function getValidCoercions(): iterable
    {
        yield ['5', 5];
        yield [5, 5];
        yield [5.0, 5];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield ['true'];
        yield ['false'];
        yield [1.2];
        yield [1];
        yield [Type\bool()];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), '5'];
        yield [Type\literal_scalar('5'), '"5"'];
        yield [Type\literal_scalar(5.5000), '5.5'];
        yield [Type\literal_scalar(false), 'false'];
        yield [Type\literal_scalar(true), 'true'];
        yield [Type\literal_scalar(5.50000000000005), '5.50000000000005'];
    }
}
