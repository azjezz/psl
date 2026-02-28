<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Override;
use Psl\Str;
use Psl\Tests\Fixture\IntegerEnum;
use Psl\Type;

use const STDIN;

/**
 * @extends TypeTestCase<value-of<IntegerEnum>>
 */
final class IntegerBackedEnumValueTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\backed_enum_value(IntegerEnum::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [static::stringable('1'), IntegerEnum::Foo->value];
        yield [1, IntegerEnum::Foo->value];
        yield ['1', IntegerEnum::Foo->value];
        yield ['2', IntegerEnum::Bar->value];
        yield [2, IntegerEnum::Bar->value];
    }

    /**
     * @return iterable<array{0: mixed}>
     */
    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [99];
        yield [null];
        yield [STDIN];
        yield ['hello'];
        yield [static::stringable('bar')];
        yield [new class {}];
    }

    /**
     * @return iterable<array{0: Type\Type<mixed>, 1: string}>
     */
    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [Type\backed_enum_value(IntegerEnum::class), Str\format('value-of<%s>', IntegerEnum::class)];
    }
}
