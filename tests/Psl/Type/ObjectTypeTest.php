<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Collection;
use Psl\Collection\ICollection;
use Psl\Type;

class ObjectTypeTest extends TypeTest
{
    public function getType(): Type\Type
    {
        return Type\object(Collection\ICollection::class);
    }

    public function getValidCoercions(): iterable
    {
        yield [$_ = new Collection\Vector([1, 2]), $_];
        yield [$_ = new Collection\MutableVector([1, 2]), $_];
        yield [$_ = new Collection\Map([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = new Collection\MutableMap([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = $this->createStub(ICollection::class), $_];
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
        yield [Type\object(Collection\IMap::class), Collection\IMap::class];
        yield [Type\object(Collection\IVector::class), Collection\IVector::class];
        yield [Type\object(Collection\Vector::class), Collection\Vector::class];
        yield [Type\object(Collection\Map::class), Collection\Map::class];
    }
}
