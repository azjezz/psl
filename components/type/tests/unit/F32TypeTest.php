<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Math;
use Psl\Type;

final class F32TypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\f32();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [123, 123.0];
        yield ['+0', 0.0];
        yield [+0, 0.0];
        yield [0, 0.0];
        yield ['0', 0.0];
        yield ['123', 123.0];
        yield [static::stringable('123'), 123.0];
        yield ['1e2', 1e2];
        yield [static::stringable('1e2'), 1e2];
        yield ['.23', .23];
        yield ['3.', 3.0];
        yield [static::stringable('1.23'), 1.23];
        yield [Math\UINT32_MAX, (float) Math\UINT32_MAX];
        yield [(string) Math\UINT32_MAX, (float) Math\UINT32_MAX];
        yield [static::stringable((string) Math\UINT32_MAX), (float) Math\UINT32_MAX];
        yield ['9223372036854775808', 9_223_372_036_854_775_808.0];
        yield ['3.40282347E+38', Math\FLOAT32_MAX];
        yield ['-3.40282347E+38', Math\FLOAT32_MIN];
        yield ['007', 7.0];
        yield ['-0.1', -0.1];
        yield ['-.5', -.5];
        yield ['-.9e2', -.9e2];
        yield ['-0.7e2', -0.7e2];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [''];
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
        yield [Math\FLOAT64_MIN];
        yield [Math\FLOAT64_MAX];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'f32'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\f32(), Type\f32());
    }
}
