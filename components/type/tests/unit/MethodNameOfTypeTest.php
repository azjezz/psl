<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Collection;
use Psl\Type;

final class MethodNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\method_name_of(Collection\VectorInterface::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield ['at', 'at'];
        yield ['get', 'get'];
        yield ['count', 'count'];
        yield ['isEmpty', 'isEmpty'];
        yield ['toArray', 'toArray'];
        yield ['filter', 'filter'];
        yield ['map', 'map'];
        yield ['first', 'first'];
        yield ['last', 'last'];
        yield ['Last', 'Last'];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentMethod'];
        yield [''];
        yield [123];
        yield [true];
        yield [[]];
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [
            Type\method_name_of(Collection\VectorInterface::class),
            'method-name-of<Psl\Collection\VectorInterface>',
        ];
        yield [Type\method_name_of(Collection\MapInterface::class), 'method-name-of<Psl\Collection\MapInterface>'];
        yield [Type\method_name_of(Collection\Vector::class), 'method-name-of<Psl\Collection\Vector>'];
    }
}
