<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Collection\ICollection;
use Psl\Collection\IIndexAccess;
use Psl\Type;

class IntersectionTypeTest extends TypeTest
{
    public function testIntersectionLeft(): void
    {
        $intersection = Type\intersection(Type\array_key(), Type\int());

        self::assertSame(1, $intersection->coerce('1'));
    }

    public function getType(): Type\Type
    {
        return Type\intersection(Type\int(), Type\array_key());
    }

    public function getValidCoercions(): iterable
    {
        yield [1, 1];
        yield ['1', 1];
        yield ['123', 123];
        yield [$this->stringable('123'), 123];
        yield [$this->stringable('000'), 0];
        yield [$this->stringable('0007'), 7];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['hello'];
        yield [$this->stringable('foo')];
        yield [new class {
        }];
    }

    public function getToStringExamples(): iterable
    {
        yield [Type\intersection(Type\object(IIndexAccess::class), Type\object(ICollection::class)), 'Psl\Collection\IIndexAccess&Psl\Collection\ICollection'];

        yield [Type\intersection(
            Type\object(IIndexAccess::class),
            Type\union(Type\object(ICollection::class), Type\object(\Iterator::class))
        ), 'Psl\Collection\IIndexAccess&(Psl\Collection\ICollection|Iterator)'];

        yield [Type\intersection(
            Type\union(Type\object(ICollection::class), Type\object(\Iterator::class)),
            Type\object(IIndexAccess::class)
        ), '(Psl\Collection\ICollection|Iterator)&Psl\Collection\IIndexAccess'];
    }
}
