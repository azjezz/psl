<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Math;
use Psl\Type;

final class NumTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\num();
    }

    public function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield ['0', 0];
        yield ['123', 123];
        yield [$this->stringable('123'), 123];
        yield [$this->stringable((string) Math\INT16_MAX), Math\INT16_MAX];
        yield [$this->stringable((string) Math\INT64_MAX), Math\INT64_MAX];
        yield [(string) Math\INT64_MAX, Math\INT64_MAX];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [$this->stringable('-321'), -321];
        yield ['-321', -321];
        yield [-321, -321];
        yield ['7', 7];
        yield ['07', 7];
        yield ['007', 7];
        yield ['000', 0];
        yield ['0', 0];
        yield ['123', 123];
        yield [$this->stringable('123'), 123];
        yield ['1e2', 1e2];
        yield [$this->stringable('1e2'), 1e2];
        yield ['1.23e45', 1.23e45];
        yield ['.23', .23];
        yield [$this->stringable('1.23'), 1.23];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [(string)Math\INT64_MAX, Math\INT64_MAX];
        yield [$this->stringable((string)Math\INT64_MAX), Math\INT64_MAX];
        yield ['9223372036854775808', 9223372036854775808.0];
        yield ['007', 7];
        yield ['-0.1', -0.1];
        yield ['-.5', -.5];
        yield ['-.9e2', -.9e2];
        yield ['-0.7e2', -0.7e2];
    }

    public function getInvalidCoercions(): iterable
    {
        yield ['foo'];
        yield [null];
        yield [false];
        yield [new class () {
        }];
        yield [$this->stringable('foo')];
        yield ['0xFF'];
        yield ['1a'];
        yield ['e1'];
        yield ['1e'];
        yield ['ee7'];
        yield ['1e2e1'];
        yield ['1ee1'];
        yield ['1,2'];
        yield ['+1'];
        yield ['3.'];
        yield [''];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'num'];
    }
}
