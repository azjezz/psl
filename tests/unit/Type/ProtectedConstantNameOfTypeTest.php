<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithConstants;
use Psl\Type;

final class ProtectedConstantNameOfTypeTest extends TypeTestCase
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\protected_constant_name_of(ClassWithConstants::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['PROTECTED_CONST', 'PROTECTED_CONST'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentConstant'];
        yield ['FOO'];
        yield ['PRIVATE_CONST'];
        yield [''];
        yield [123];
        yield [true];
        yield [[]];
        yield [$this->stringable('foo')];
        yield [new class {}];
    }

    #[\Override]
    public function getToStringExamples(): iterable
    {
        yield [
            Type\protected_constant_name_of(ClassWithConstants::class),
            'protected-constant-name-of<Psl\Tests\Fixture\ClassWithConstants>',
        ];
    }
}
