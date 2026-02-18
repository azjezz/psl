<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Override;
use Psl\Tests\Fixture\StringEnum;
use Psl\Tests\Fixture\UnitEnum;
use Psl\Type;

final class EnumCaseOfTypeTest extends TypeTestCase
{
    #[Override]
    public function getType(): Type\TypeInterface
    {
        return Type\enum_case_of(UnitEnum::class);
    }

    #[Override]
    public function getValidCoercions(): iterable
    {
        yield ['Foo', 'Foo'];
        yield ['Bar', 'Bar'];
        yield ['Baz', 'Baz'];
    }

    #[Override]
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentCase'];
        yield [''];
        yield [123];
        yield [true];
        yield [[]];
        yield [$this->stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public function getToStringExamples(): iterable
    {
        yield [
            Type\enum_case_of(UnitEnum::class),
            'enum-case-of<Psl\Tests\Fixture\UnitEnum>',
        ];
        yield [
            Type\enum_case_of(StringEnum::class),
            'enum-case-of<Psl\Tests\Fixture\StringEnum>',
        ];
    }
}
