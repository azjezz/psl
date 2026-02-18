<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithConstants;
use Psl\Type;

final class PrivateConstantNameOfTypeTest extends TypeTestCase
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\private_constant_name_of(ClassWithConstants::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['PRIVATE_CONST', 'PRIVATE_CONST'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentConstant'];
        yield ['FOO'];
        yield ['PROTECTED_CONST'];
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
            Type\private_constant_name_of(ClassWithConstants::class),
            'private-constant-name-of<Psl\Tests\Fixture\ClassWithConstants>',
        ];
    }
}
