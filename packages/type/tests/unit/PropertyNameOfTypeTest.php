<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\ClassWithProperties;

final class PropertyNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\property_name_of(ClassWithProperties::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['publicProperty', 'publicProperty'];
        yield ['protectedProperty', 'protectedProperty'];
        yield ['privateProperty', 'privateProperty'];
        yield ['staticProperty', 'staticProperty'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentProperty'];
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
            Type\property_name_of(ClassWithProperties::class),
            'property-name-of<Psl\Type\Tests\Fixture\ClassWithProperties>',
        ];
    }
}
