<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Collection\CollectionInterface;
use Psl\Type;

final class InstanceOfTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\instance_of(Collection\CollectionInterface::class);
    }

    public function getValidCoercions(): iterable
    {
        yield [$_ = new Collection\Vector([1, 2]), $_];
        yield [$_ = new Collection\MutableVector([1, 2]), $_];
        yield [$_ = new Collection\Map([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = new Collection\MutableMap([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = $this->createStub(CollectionInterface::class), $_];
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
        yield [Type\instance_of(Collection\MapInterface::class), Collection\MapInterface::class];
        yield [Type\instance_of(Collection\VectorInterface::class), Collection\VectorInterface::class];
        yield [Type\instance_of(Collection\Vector::class), Collection\Vector::class];
        yield [Type\instance_of(Collection\Map::class), Collection\Map::class];
    }
}
