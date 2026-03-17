<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Str;
use Psl\Type;
use Psl\Type\Tests\Fixture\StringEnum;

/**
 * @extends TypeTestCase<StringEnum>
 */
final class StringBackedEnumTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\backed_enum(StringEnum::class);
    }

    /**
     * @return iterable<array{0: mixed, 1: StringEnum}>
     */
    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [StringEnum::Foo, StringEnum::Foo];
        yield [static::stringable('foo'), StringEnum::Foo];
        yield ['foo', StringEnum::Foo];
        yield ['1', StringEnum::Bar];
        yield [1, StringEnum::Bar];
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
     * @return iterable<array{0: Type\Type<mixed>, 1: string}>
     */
    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [Type\backed_enum(StringEnum::class), Str\format('backed-enum(%s)', StringEnum::class)];
    }
}
