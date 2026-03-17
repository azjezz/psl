<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\ClassWithConstants;

final class PublicConstantNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\public_constant_name_of(ClassWithConstants::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['FOO', 'FOO'];
        yield ['BAR', 'BAR'];
        yield ['BAZ', 'BAZ'];
        yield ['QUX', 'QUX'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
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
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [
            Type\public_constant_name_of(ClassWithConstants::class),
            'public-constant-name-of<Psl\Type\Tests\Fixture\ClassWithConstants>',
        ];
    }
}
