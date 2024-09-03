<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Str;
use Psl\Tests\Fixture\StringEnum;
use Psl\Type;

use const STDIN;

/**
 * @extends TypeTest<value-of<StringEnum>>
 */
final class StringBackedEnumValueTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\backed_enum_value(StringEnum::class);
    }

    public function getValidCoercions(): iterable
    {
        yield [1, StringEnum::Bar->value];
        yield [$this->stringable('foo'), StringEnum::Foo->value];
        yield ['foo', StringEnum::Foo->value];
        yield ['1', StringEnum::Bar->value];
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
     * @return iterable<array{0: Type\Type<value-of<StringEnum>>, 1: string}>
     */
    public function getToStringExamples(): iterable
    {
        yield [Type\backed_enum_value(StringEnum::class), Str\format('value-of<%s>', StringEnum::class)];
    }
}
