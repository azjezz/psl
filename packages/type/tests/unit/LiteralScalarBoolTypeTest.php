<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

/**
 * @extends TypeTestCase<false>
 */
final class LiteralScalarBoolTypeTest extends TypeTestCase<bool>
{
    #[Override]
    public static function getType(): Type\TypeInterface<bool>
    {
        return Type\literal_scalar::<bool>(false);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['0', false];
        yield [0, false];
        yield [false, false];
        yield ['false', false];
        yield ['False', false];
        yield ['FALSE', false];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [true];
        yield ['true'];
        yield ['True'];
        yield ['TRUE'];
        yield [1.2];
        yield [Type\bool()];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'false'];
        yield [Type\literal_scalar::<string>('5'), '"5"'];
        yield [Type\literal_scalar::<float>(5.500_0), '5.5'];
        yield [Type\literal_scalar::<bool>(true), 'true'];
        yield [Type\literal_scalar::<float>(5.500_000_000_000_05), '5.50000000000005'];
    }
}
