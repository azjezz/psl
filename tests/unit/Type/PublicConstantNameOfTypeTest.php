<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithConstants;
use Psl\Type;

final class PublicConstantNameOfTypeTest extends TypeTestCase
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\public_constant_name_of(ClassWithConstants::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['FOO', 'FOO'];
        yield ['BAR', 'BAR'];
        yield ['BAZ', 'BAZ'];
        yield ['QUX', 'QUX'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentConstant'];
        yield ['PROTECTED_CONST'];
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
            Type\public_constant_name_of(ClassWithConstants::class),
            'public-constant-name-of<Psl\Tests\Fixture\ClassWithConstants>',
        ];
    }
}
