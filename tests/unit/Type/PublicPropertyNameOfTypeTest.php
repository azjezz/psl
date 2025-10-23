<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithProperties;
use Psl\Type;

final class PublicPropertyNameOfTypeTest extends TypeTest
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\public_property_name_of(ClassWithProperties::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['publicProperty', 'publicProperty'];
        yield ['staticProperty', 'staticProperty'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentProperty'];
        yield ['protectedProperty'];
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
            Type\public_property_name_of(ClassWithProperties::class),
            'public-property-name-of<Psl\Tests\Fixture\ClassWithProperties>',
        ];
    }
}
