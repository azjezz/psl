<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Math;
use Psl\Type;

final class AlwaysAssertTypeTest extends TypeTest
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\always_assert(Type\int());
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [-321, -321];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
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
        yield ['9223372036854775808'];
        yield [$this->stringable('9223372036854775808')];
        yield ['-9223372036854775809'];
        yield [$this->stringable('-9223372036854775809')];
        yield ['0xFF'];
        yield [''];
        yield ['123'];
        yield ['0'];
        yield [$this->stringable('123')];
        yield [$this->stringable((string) Math\INT16_MAX)];
        yield [$this->stringable((string) Math\INT64_MAX)];
        yield [(string) Math\INT64_MAX];
        yield [$this->stringable('-321')];
        yield ['-321'];
        yield ['7'];
        yield ['07'];
        yield ['007'];
        yield ['000'];
        yield [1.0];
    }

    #[\Override]
    public function getToStringExamples(): iterable
    {
        yield [Type\always_assert(Type\int()), 'int'];
        yield [Type\always_assert(Type\string()), 'string'];
        yield [Type\always_assert(Type\bool()), 'bool'];
    }
}
