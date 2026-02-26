<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Tests\Fixture\ClassWithMethods;
use Psl\Type;

final class PublicMethodNameOfTypeTest extends TypeTestCase
{
    #[\Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\public_method_name_of(ClassWithMethods::class);
    }

    #[\Override]
    public static function getValidCoercions(): iterable
    {
        yield ['publicMethod', 'publicMethod'];
        yield ['publicStaticMethod', 'publicStaticMethod'];
        yield ['PUBLICMETHOD', 'PUBLICMETHOD'];
        yield ['PublicMethod', 'PublicMethod'];
    }

    #[\Override]
    public static function getInvalidCoercions(): iterable
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
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[\Override]
    public static function getToStringExamples(): iterable
    {
        yield [
            Type\public_method_name_of(ClassWithMethods::class),
            'public-method-name-of<Psl\Tests\Fixture\ClassWithMethods>',
        ];
    }
}
