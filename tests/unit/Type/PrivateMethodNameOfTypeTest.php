<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithMethods;
use Psl\Type;

final class PrivateMethodNameOfTypeTest extends TypeTest
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\private_method_name_of(ClassWithMethods::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['privateMethod', 'privateMethod'];
        yield ['PrivateMethod', 'PrivateMethod'];
        yield ['PRIVATEMETHOD', 'PRIVATEMETHOD'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentMethod'];
        yield ['publicMethod'];
        yield ['protectedMethod'];
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
            Type\private_method_name_of(ClassWithMethods::class),
            'private-method-name-of<Psl\Tests\Fixture\ClassWithMethods>',
        ];
    }
}
