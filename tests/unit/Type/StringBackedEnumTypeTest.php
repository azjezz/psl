<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Str;
use Psl\Tests\Fixture\StringEnum;
use Psl\Type;

/**
 * @extends TypeTest<StringEnum>
 */
final class StringBackedEnumTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\backed_enum(StringEnum::class);
    }

    /**
     * @return iterable<array{0: mixed, 1: StringEnum}>
     */
    public function getValidCoercions(): iterable
    {
        yield [StringEnum::Foo, StringEnum::Foo];
        yield [$this->stringable('foo'), StringEnum::Foo];
        yield ['foo', StringEnum::Foo];
        yield ['1', StringEnum::Bar];
        yield [1, StringEnum::Bar];
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
        yield [Type\backed_enum(StringEnum::class), Str\format('backed-enum(%s)', StringEnum::class)];
    }
}
