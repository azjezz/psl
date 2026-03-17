<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Math;
use Psl\Type;

final class AlwaysAssertTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\always_assert(Type\int());
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [-321, -321];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [1.23];
        yield ['1.23'];
        yield ['1e123'];
        yield [''];
        yield [[]];
        yield [[123]];
        yield [null];
        yield [false];
        yield [static::stringable('1.23')];
        yield [static::stringable('-007')];
        yield ['-007'];
        yield ['9223372036854775808'];
        yield [static::stringable('9223372036854775808')];
        yield ['-9223372036854775809'];
        yield [static::stringable('-9223372036854775809')];
        yield ['0xFF'];
        yield [''];
        yield ['123'];
        yield ['0'];
        yield [static::stringable('123')];
        yield [static::stringable((string) Math\INT16_MAX)];
        yield [static::stringable((string) Math\INT64_MAX)];
        yield [(string) Math\INT64_MAX];
        yield [static::stringable('-321')];
        yield ['-321'];
        yield ['7'];
        yield ['07'];
        yield ['007'];
        yield ['000'];
        yield [1.0];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [Type\always_assert(Type\int()), 'int'];
        yield [Type\always_assert(Type\string()), 'string'];
        yield [Type\always_assert(Type\bool()), 'bool'];
    }
}
