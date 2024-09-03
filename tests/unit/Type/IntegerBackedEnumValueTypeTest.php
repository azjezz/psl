<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Str;
use Psl\Tests\Fixture\IntegerEnum;
use Psl\Type;

use const STDIN;

/**
 * @extends TypeTest<value-of<IntegerEnum>>
 */
final class IntegerBackedEnumValueTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\backed_enum_value(IntegerEnum::class);
    }

    public function getValidCoercions(): iterable
    {
        yield [$this->stringable('1'), IntegerEnum::Foo->value];
        yield [1, IntegerEnum::Foo->value];
        yield ['1', IntegerEnum::Foo->value];
        yield ['2', IntegerEnum::Bar->value];
        yield [2, IntegerEnum::Bar->value];
    }

    /**
     * @return iterable<array{0: mixed}>
     */
    public function getInvalidCoercions(): iterable
    {
        yield [99];
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
        yield [Type\backed_enum_value(IntegerEnum::class), Str\format('value-of<%s>', IntegerEnum::class)];
    }
}
