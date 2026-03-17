<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Str;
use Psl\Type;
use Psl\Type\Tests\Fixture\IntegerEnum;

/**
 * @extends TypeTestCase<IntegerEnum>
 */
final class IntegerBackedEnumTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\backed_enum(IntegerEnum::class);
    }

    /**
     * @return iterable<array{0: mixed, 1: IntegerEnum}>
     */
    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [IntegerEnum::Foo, IntegerEnum::Foo];
        yield [static::stringable('1'), IntegerEnum::Foo];
        yield [1, IntegerEnum::Foo];
        yield ['1', IntegerEnum::Foo];
        yield ['2', IntegerEnum::Bar];
        yield [2, IntegerEnum::Bar];
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
        yield [Type\backed_enum(IntegerEnum::class), Str\format('backed-enum(%s)', IntegerEnum::class)];
    }
}
