<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\ClassWithConstants;

final class PrivateConstantNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\private_constant_name_of(ClassWithConstants::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['PRIVATE_CONST', 'PRIVATE_CONST'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentConstant'];
        yield ['FOO'];
        yield ['PROTECTED_CONST'];
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
            Type\private_constant_name_of(ClassWithConstants::class),
            'private-constant-name-of<Psl\Type\Tests\Fixture\ClassWithConstants>',
        ];
    }
}
