<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithProperties;
use Psl\Type;

final class PrivatePropertyNameOfTypeTest extends TypeTestCase
{
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\private_property_name_of(ClassWithProperties::class);
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['privateProperty', 'privateProperty'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentProperty'];
        yield ['publicProperty'];
        yield ['protectedProperty'];
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
            Type\private_property_name_of(ClassWithProperties::class),
            'private-property-name-of<Psl\Tests\Fixture\ClassWithProperties>',
        ];
    }
}
