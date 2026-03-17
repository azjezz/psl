<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;
use Psl\Type\Tests\Fixture\StringEnum;
use Psl\Type\Tests\Fixture\UnitEnum;

final class EnumCaseOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\enum_case_of(UnitEnum::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['Foo', 'Foo'];
        yield ['Bar', 'Bar'];
        yield ['Baz', 'Baz'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentCase'];
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
            Type\enum_case_of(UnitEnum::class),
            'enum-case-of<Psl\Type\Tests\Fixture\UnitEnum>',
        ];
        yield [
            Type\enum_case_of(StringEnum::class),
            'enum-case-of<Psl\Type\Tests\Fixture\StringEnum>',
        ];
    }
}
