<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Math;
use Psl\Type;

final class U8TypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\u8();
    }

    public function getValidCoercions(): iterable
    {
        yield [255, 255];
        yield [0, 0];
        yield ['0', 0];
        yield ['255', 255];
        yield [$this->stringable('255'), 255];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield ['000', 0];
        yield [1.0, 1];
        yield [255.0, 255];
    }

    public function getInvalidCoercions(): iterable
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
        yield [$this->stringable('1.23')];
        yield [$this->stringable('-007')];
        yield ['-007'];
        yield ['256'];
        yield [$this->stringable('256')];
        yield ['-255'];
        yield [$this->stringable('-255')];
        yield ['0xFF'];
        yield [''];
        yield [-255];
        yield [$this->stringable((string) Math\INT16_MAX)];
        yield [$this->stringable((string) Math\INT16_MIN)];
        yield [$this->stringable((string) Math\INT32_MAX)];
        yield [$this->stringable((string) Math\INT32_MIN)];
        yield [$this->stringable((string) Math\INT64_MAX)];
        yield [$this->stringable((string) Math\INT64_MIN)];
        yield [$this->stringable((string) Math\UINT16_MAX)];
        yield [$this->stringable((string) Math\UINT32_MAX)];
        yield [(string) Math\INT64_MAX];
        yield [Math\INT64_MAX];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'u8'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\u8(), Type\u8());
    }
}
