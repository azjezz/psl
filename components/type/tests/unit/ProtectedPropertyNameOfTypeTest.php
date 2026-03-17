<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\ClassWithProperties;

final class ProtectedPropertyNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\protected_property_name_of(ClassWithProperties::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['protectedProperty', 'protectedProperty'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
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
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [
            Type\protected_property_name_of(ClassWithProperties::class),
            'protected-property-name-of<Psl\Type\Tests\Fixture\ClassWithProperties>',
        ];
    }
}
