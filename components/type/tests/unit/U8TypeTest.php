<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Math;
use Psl\Type;

final class U8TypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\u8();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [255, 255];
        yield [0, 0];
        yield ['0', 0];
        yield ['255', 255];
        yield [static::stringable('255'), 255];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield ['000', 0];
        yield [1.0, 1];
        yield [255.0, 255];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [-1];
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
        yield ['256'];
        yield [static::stringable('256')];
        yield ['-255'];
        yield [static::stringable('-255')];
        yield ['0xFF'];
        yield [''];
        yield [-255];
        yield [static::stringable((string) Math\INT16_MAX)];
        yield [static::stringable((string) Math\INT16_MIN)];
        yield [static::stringable((string) Math\INT32_MAX)];
        yield [static::stringable((string) Math\INT32_MIN)];
        yield [static::stringable((string) Math\INT64_MAX)];
        yield [static::stringable((string) Math\INT64_MIN)];
        yield [static::stringable((string) Math\UINT16_MAX)];
        yield [static::stringable((string) Math\UINT32_MAX)];
        yield [(string) Math\INT64_MAX];
        yield [Math\INT64_MAX];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'u8'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\u8(), Type\u8());
    }
}
