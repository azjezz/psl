<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

final class LiteralScalarIntTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\literal_scalar(5);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['5', 5];
        yield [5, 5];
        yield [5.0, 5];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield ['true'];
        yield ['false'];
        yield [1.2];
        yield [1];
        yield [Type\bool()];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), '5'];
        yield [Type\literal_scalar('5'), '"5"'];
        yield [Type\literal_scalar(5.500_0), '5.5'];
        yield [Type\literal_scalar(false), 'false'];
        yield [Type\literal_scalar(true), 'true'];
        yield [Type\literal_scalar(5.500_000_000_000_05), '5.50000000000005'];
    }
}
