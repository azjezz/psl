<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Str;
use Psl\Tests\Fixture\UnitEnum;
use Psl\Type;

/**
 * @extends TypeTest<UnitEnum>
 */
final class UnitEnumTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\unit_enum(UnitEnum::class);
    }

    /**
     * @return iterable<array{0: mixed, 1: UnitEnum}>
     */
    public function getValidCoercions(): iterable
    {
        yield [UnitEnum::Foo, UnitEnum::Foo];
        yield [UnitEnum::Bar, UnitEnum::Bar];
        yield [UnitEnum::Baz, UnitEnum::Baz];
    }

    /**
     * @return iterable<array{0: mixed}>
     */
    public function getInvalidCoercions(): iterable
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
        yield [$this->stringable('bar')];
        yield [new class {
        }];
    }

    /**
     * @return iterable<array{0: Type\Type<mixed>, 1: string}>
     */
    public function getToStringExamples(): iterable
    {
        yield [Type\unit_enum(UnitEnum::class), Str\format('unit-enum(%s)', UnitEnum::class)];
    }
}
