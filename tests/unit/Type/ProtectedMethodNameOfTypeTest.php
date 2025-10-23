<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithMethods;
use Psl\Type;

final class ProtectedMethodNameOfTypeTest extends TypeTest
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\protected_method_name_of(ClassWithMethods::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['protectedMethod', 'protectedMethod'];
        yield ['ProtectedMethod', 'ProtectedMethod'];
        yield ['PROTECTEDMETHOD', 'PROTECTEDMETHOD'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentMethod'];
        yield ['publicMethod'];
        yield ['privateMethod'];
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
            Type\protected_method_name_of(ClassWithMethods::class),
            'protected-method-name-of<Psl\Tests\Fixture\ClassWithMethods>',
        ];
    }
}
