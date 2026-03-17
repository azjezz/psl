<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\ClassWithProperties;

final class PublicPropertyNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\public_property_name_of(ClassWithProperties::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['publicProperty', 'publicProperty'];
        yield ['staticProperty', 'staticProperty'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
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
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [
            Type\public_property_name_of(ClassWithProperties::class),
            'public-property-name-of<Psl\Type\Tests\Fixture\ClassWithProperties>',
        ];
    }
}
