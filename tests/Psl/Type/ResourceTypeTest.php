<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Math;
use Psl\Type;

final class ResourceTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\scalar();
    }

    public function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield ['0', '0'];
        yield ['123', '123'];
        yield [$this->stringable('123'), '123'];
        yield [$this->stringable((string) Math\INT16_MAX), (string) Math\INT16_MAX];
        yield [$this->stringable((string) Math\INT64_MAX), (string) Math\INT64_MAX];
        yield [(string) Math\INT64_MAX, (string) Math\INT64_MAX];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [$this->stringable('-321'), '-321'];
        yield ['-321', '-321'];
        yield [-321, -321];
        yield ['7', '7'];
        yield ['07', '07'];
        yield ['007', '007'];
        yield ['000', '000'];
        yield [$this->stringable('123'), '123'];
        yield ['1e2', '1e2'];
        yield [$this->stringable('1e2'), '1e2'];
        yield ['1.23e45', '1.23e45'];
        yield ['.23', '.23'];
        yield [$this->stringable('1.23'), '1.23'];
        yield [(float) Math\INT64_MAX, (float) Math\INT64_MAX];
        yield ['9223372036854775808', '9223372036854775808'];
        yield ['-.9e2', '-.9e2'];
        yield ['-0.7e2', '-0.7e2'];
        yield [false, false];
        yield [0, 0];
        yield [1, 1];
        yield [true, true];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [new class {
        }];
        yield [STDIN];
        yield [[]];
        yield [(static fn () => yield 'hello')()];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'scalar'];
    }
}
