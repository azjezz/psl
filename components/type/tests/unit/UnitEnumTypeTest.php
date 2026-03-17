<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Str;
use Psl\Type;
use Psl\Type\Tests\Fixture\UnitEnum;

/**
 * @extends TypeTestCase<UnitEnum>
 */
final class UnitEnumTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\unit_enum(UnitEnum::class);
    }

    /**
     * @return iterable<array{0: mixed, 1: UnitEnum}>
     */
    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [UnitEnum::Foo, UnitEnum::Foo];
        yield [UnitEnum::Bar, UnitEnum::Bar];
        yield [UnitEnum::Baz, UnitEnum::Baz];
    }

    /**
     * @return iterable<array{0: mixed}>
     */
    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        // NOTE(azjezz): should we allow coercion of unit enums from case names?
        yield ['Foo'];
        yield ['Bar'];
        yield ['Baz'];
        // or maybe from position?
        yield [1];
        yield [2];
        yield [3];
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
        yield [Type\unit_enum(UnitEnum::class), Str\format('unit-enum(%s)', UnitEnum::class)];
    }
}
