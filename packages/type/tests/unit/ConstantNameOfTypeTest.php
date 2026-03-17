<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\ClassWithConstants;

final class ConstantNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\constant_name_of(ClassWithConstants::class);
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
            Type\constant_name_of(ClassWithConstants::class),
            'constant-name-of<Psl\Type\Tests\Fixture\ClassWithConstants>',
        ];
    }
}
