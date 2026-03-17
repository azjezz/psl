<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Str;
use Psl\Type;
use Psl\Type\Tests\Fixture\StringEnum;

use const STDIN;

/**
 * @extends TypeTestCase<value-of<StringEnum>>
 */
final class StringBackedEnumValueTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\backed_enum_value(StringEnum::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [1, StringEnum::Bar->value];
        yield [static::stringable('foo'), StringEnum::Foo->value];
        yield ['foo', StringEnum::Foo->value];
        yield ['1', StringEnum::Bar->value];
    }

    /**
     * @return iterable<array{0: mixed}>
     */
    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['hello'];
        yield [static::stringable('bar')];
        yield [new class {}];
    }

    /**
     * @return iterable<array{0: Type\Type<value-of<StringEnum>>, 1: string}>
     */
    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [Type\backed_enum_value(StringEnum::class), Str\format('value-of<%s>', StringEnum::class)];
    }
}
