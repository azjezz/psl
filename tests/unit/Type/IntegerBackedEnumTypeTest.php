<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Str;
use Psl\Tests\Fixture\IntegerEnum;
use Psl\Type;

/**
 * @extends TypeTest<IntegerEnum>
 */
final class IntegerBackedEnumTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\backed_enum(IntegerEnum::class);
    }

    /**
     * @return iterable<array{0: mixed, 1: IntegerEnum}>
     */
    public function getValidCoercions(): iterable
    {
        yield [IntegerEnum::Foo, IntegerEnum::Foo];
        yield [$this->stringable('1'), IntegerEnum::Foo];
        yield [1, IntegerEnum::Foo];
        yield ['1', IntegerEnum::Foo];
        yield ['2', IntegerEnum::Bar];
        yield [2, IntegerEnum::Bar];
    }

    /**
     * @return iterable<array{0: mixed}>
     */
    public function getInvalidCoercions(): iterable
    {
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
        yield [Type\backed_enum(IntegerEnum::class), Str\format('backed-enum(%s)', IntegerEnum::class)];
    }
}
