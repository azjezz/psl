<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Math;
use Psl\Type;

final class U32TypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\u32();
    }

    public function getValidCoercions(): iterable
    {
        yield [4294967295, 4294967295];
        yield [0, 0];
        yield ['0', 0];
        yield ['123', 123];
        yield [$this->stringable('4294967295'), 4294967295];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield ['000', 0];
        yield [1.0, 1];
        yield [$this->stringable((string) Math\UINT8_MAX), Math\UINT8_MAX];
        yield [$this->stringable((string) Math\UINT16_MAX), Math\UINT16_MAX];
        yield [$this->stringable((string) Math\UINT32_MAX), Math\UINT32_MAX];
        yield [$this->stringable((string) Math\INT32_MAX), Math\INT32_MAX];
        yield [$this->stringable((string) Math\INT16_MAX), Math\INT16_MAX];
        yield [$this->stringable((string) Math\INT8_MAX), Math\INT8_MAX];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [-4294967295];
        yield [1.23];
        yield ['1.23'];
        yield ['1e123'];
        yield [''];
        yield [[]];
        yield [[4294967295]];
        yield [null];
        yield [false];
        yield [$this->stringable('1.23')];
        yield [$this->stringable('-123')];
        yield [$this->stringable('-007')];
        yield ['-007'];
        yield ['4294967296'];
        yield [$this->stringable('-4294967295')];
        yield ['0xFF'];
        yield [''];
        yield [$this->stringable((string) Math\INT64_MAX)];
        yield [$this->stringable((string) Math\INT64_MIN)];
        yield [(string) Math\INT64_MAX];
        yield [Math\INT64_MAX];
        yield [Math\INT64_MIN];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'u32'];
    }
}
