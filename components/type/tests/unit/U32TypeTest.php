<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Math;
use Psl\Type;

final class U32TypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\u32();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [4_294_967_295, 4_294_967_295];
        yield [0, 0];
        yield ['0', 0];
        yield ['123', 123];
        yield [static::stringable('4294967295'), 4_294_967_295];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield ['000', 0];
        yield [1.0, 1];
        yield [static::stringable((string) Math\UINT8_MAX), Math\UINT8_MAX];
        yield [static::stringable((string) Math\UINT16_MAX), Math\UINT16_MAX];
        yield [static::stringable((string) Math\UINT32_MAX), Math\UINT32_MAX];
        yield [static::stringable((string) Math\INT32_MAX), Math\INT32_MAX];
        yield [static::stringable((string) Math\INT16_MAX), Math\INT16_MAX];
        yield [static::stringable((string) Math\INT8_MAX), Math\INT8_MAX];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [-4_294_967_295];
        yield [1.23];
        yield ['1.23'];
        yield ['1e123'];
        yield [''];
        yield [[]];
        yield [[4_294_967_295]];
        yield [null];
        yield [false];
        yield [static::stringable('1.23')];
        yield [static::stringable('-123')];
        yield [static::stringable('-007')];
        yield ['-007'];
        yield ['4294967296'];
        yield [static::stringable('-4294967295')];
        yield ['0xFF'];
        yield [''];
        yield [static::stringable((string) Math\INT64_MAX)];
        yield [static::stringable((string) Math\INT64_MIN)];
        yield [(string) Math\INT64_MAX];
        yield [Math\INT64_MAX];
        yield [Math\INT64_MIN];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'u32'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\u32(), Type\u32());
    }
}
