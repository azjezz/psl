<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Math;
use Psl\Type;

/**
 * @extends TypeTest<positive-int>
 */
final class PositiveIntTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\positive_int();
    }

    public function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield ['123', 123];
        yield [$this->stringable('123'), 123];
        yield [$this->stringable((string) Math\INT16_MAX), Math\INT16_MAX];
        yield [$this->stringable((string) Math\INT64_MAX), Math\INT64_MAX];
        yield [(string) Math\INT64_MAX, Math\INT64_MAX];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield [1.0, 1];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [0];
        yield ['0'];
        yield ['-321'];
        yield [-321];
        yield ['000'];
        yield [1.23];
        yield ['1.23'];
        yield ['1e123'];
        yield ['-1e123'];
        yield [''];
        yield [[]];
        yield [[123]];
        yield [null];
        yield [false];
        yield [$this->stringable('1.23')];
        yield [$this->stringable('-007')];
        yield [$this->stringable('-321')];
        yield ['-007'];
        yield ['9223372036854775808'];
        yield [$this->stringable('9223372036854775808')];
        yield ['-9223372036854775809'];
        yield [$this->stringable('-9223372036854775809')];
        yield ['0xFF'];
        yield ['-0xFF'];
        yield ["\xc1\xbf"];
        yield [''];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'positive-int'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\positive_int(), Type\positive_int());
    }
}
