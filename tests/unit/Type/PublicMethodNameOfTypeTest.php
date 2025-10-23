<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithMethods;
use Psl\Type;

final class PublicMethodNameOfTypeTest extends TypeTest
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\public_method_name_of(ClassWithMethods::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['publicMethod', 'publicMethod'];
        yield ['publicStaticMethod', 'publicStaticMethod'];
        yield ['PUBLICMETHOD', 'PUBLICMETHOD'];
        yield ['PublicMethod', 'PublicMethod'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentMethod'];
        yield ['protectedMethod'];
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
            Type\public_method_name_of(ClassWithMethods::class),
            'public-method-name-of<Psl\Tests\Fixture\ClassWithMethods>',
        ];
    }
}
