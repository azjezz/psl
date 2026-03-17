<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\ClassWithMethods;

final class PrivateMethodNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\private_method_name_of(ClassWithMethods::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['privateMethod', 'privateMethod'];
        yield ['PrivateMethod', 'PrivateMethod'];
        yield ['PRIVATEMETHOD', 'PRIVATEMETHOD'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
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
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [
            Type\private_method_name_of(ClassWithMethods::class),
            'private-method-name-of<Psl\Type\Tests\Fixture\ClassWithMethods>',
        ];
    }
}
