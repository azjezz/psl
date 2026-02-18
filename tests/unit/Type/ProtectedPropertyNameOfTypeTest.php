<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithProperties;
use Psl\Type;

final class ProtectedPropertyNameOfTypeTest extends TypeTestCase
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\protected_property_name_of(ClassWithProperties::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['protectedProperty', 'protectedProperty'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentProperty'];
        yield ['publicProperty'];
        yield ['privateProperty'];
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
            Type\protected_property_name_of(ClassWithProperties::class),
            'protected-property-name-of<Psl\Tests\Fixture\ClassWithProperties>',
        ];
    }
}
