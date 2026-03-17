<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Math;
use Psl\Type;

final class NumTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\num();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield ['0', 0];
        yield ['123', 123];
        yield [static::stringable('123'), 123];
        yield [static::stringable((string) Math\INT16_MAX), Math\INT16_MAX];
        yield [static::stringable((string) Math\INT64_MAX), Math\INT64_MAX];
        yield [(string) Math\INT64_MAX, Math\INT64_MAX];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [static::stringable('-321'), -321];
        yield ['-321', -321];
        yield [-321, -321];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield ['000', 0];
        yield ['0', 0];
        yield ['123', 123];
        yield [static::stringable('123'), 123];
        yield ['1e2', 1e2];
        yield [static::stringable('1e2'), 1e2];
        yield ['1.23e45', 1.23e45];
        yield ['1.23e-45', 1.23e-45];
        yield ['1.23e+45', 1.23e+45];
        yield ['.23', .23];
        yield ['3.', 3.0];
        yield [static::stringable('1.23'), 1.23];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [(string) Math\INT64_MAX, Math\INT64_MAX];
        yield [static::stringable((string) Math\INT64_MAX), Math\INT64_MAX];
        yield ['9223372036854775808', 9_223_372_036_854_775_808.0];
        yield ['007', 7];
        yield ['-0.1', -0.1];
        yield ['-.5', -.5];
        yield ['-.9e2', -.9e2];
        yield ['-0.7e2', -0.7e2];
        yield ['1.23e45', 1.23e45];
        yield ['1.23e-45', 1.23e-45];
        yield ['-33.e-1', -33.e-1];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield ['foo'];
        yield [null];
        yield [false];
        yield [new class() {}];
        yield [static::stringable('foo')];
        yield ['0xFF'];
        yield ['1a'];
        yield ['e1'];
        yield ['1e'];
        yield ['ee7'];
        yield ['1e2e1'];
        yield ['1ee1'];
        yield ['1,2'];
        yield [''];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'num'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\num(), Type\num());
    }
}
