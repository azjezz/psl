<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Override;
use Psl\Collection;
use Psl\Type;

final class MethodNameOfTypeTest extends TypeTestCase
{
    #[Override]
    public function getType(): Type\TypeInterface
    {
        return Type\method_name_of(Collection\VectorInterface::class);
    }

    #[Override]
    public function getValidCoercions(): iterable
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
    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['nonExistentMethod'];
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
            Type\method_name_of(Collection\VectorInterface::class),
            'method-name-of<Psl\Collection\VectorInterface>',
        ];
        yield [Type\method_name_of(Collection\MapInterface::class), 'method-name-of<Psl\Collection\MapInterface>'];
        yield [Type\method_name_of(Collection\Vector::class), 'method-name-of<Psl\Collection\Vector>'];
    }
}
