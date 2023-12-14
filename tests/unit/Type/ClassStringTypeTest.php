<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Type;

final class ClassStringTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\class_string(Collection\CollectionInterface::class);
    }

    public function getValidCoercions(): iterable
    {
        yield [$_ = Collection\Vector::class, $_];
        yield [$_ = Collection\MutableVector::class, $_];
        yield [$_ = Collection\Map::class, $_];
        yield [$_ = Collection\MutableMap::class, $_];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['UnknownClass'];
        yield [$this->stringable('foo')];
        yield [new class {
        }];
    }

    public function getToStringExamples(): iterable
    {
        yield [Type\class_string(Collection\MapInterface::class), 'class-string<Psl\Collection\MapInterface>'];
        yield [Type\class_string(Collection\VectorInterface::class), 'class-string<Psl\Collection\VectorInterface>'];
        yield [Type\class_string(Collection\Vector::class), 'class-string<Psl\Collection\Vector>'];
        yield [Type\class_string(Collection\Map::class), 'class-string<Psl\Collection\Map>'];
    }
}
