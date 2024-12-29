<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Math;
use Psl\Type;

final class F64TypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\f64();
    }

    public function getValidCoercions(): iterable
    {
        yield [123, 123.0];
        yield ['+0', 0.0];
        yield [+0, 0.0];
        yield [0, 0.0];
        yield ['0', 0.0];
        yield ['123', 123.0];
        yield [$this->stringable('123'), 123.0];
        yield ['1e2', 1e2];
        yield [$this->stringable('1e2'), 1e2];
        yield ['1.23e45', 1.23e45];
        yield ['1.23e-45', 1.23e-45];
        yield ['1.23e+45', 1.23e+45];
        yield ['.23', .23];
        yield ['3.', 3.0];
        yield [$this->stringable('1.23'), 1.23];
        yield [Math\UINT32_MAX, (float) Math\UINT32_MAX];
        yield [(string) Math\UINT32_MAX, (float) Math\UINT32_MAX];
        yield [$this->stringable((string) Math\UINT32_MAX), (float) Math\UINT32_MAX];
        yield ['9223372036854775808', 9223372036854775808.0];
        yield ['3.40282347E+38', Math\FLOAT32_MAX];
        yield ['-3.40282347E+38', Math\FLOAT32_MIN];
        yield ['1.7976931348623157E+308', Math\FLOAT64_MAX];
        yield ['-1.7976931348623157E+308', Math\FLOAT64_MIN];
        yield ['007', 7.0];
        yield ['-0.1', -0.1];
        yield ['-.5', -.5];
        yield ['-.9e2', -.9e2];
        yield ['-0.7e2', -0.7e2];
        yield ['1.23e45', 1.23e45];
        yield ['1.23e-45', 1.23e-45];
        yield ['-33.e-1', -33.e-1];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [''];
        yield ['foo'];
        yield [null];
        yield [false];
        yield [new class() {
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
        yield [''];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'f64'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\f64(), Type\f64());
    }
}
