<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use Psl\Collection;
use Psl\Collection\CollectionInterface;
use Psl\Type;

final class ObjectTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\object();
    }

    public function getValidCoercions(): iterable
    {
        yield [$_ = new Collection\Vector([1, 2]), $_];
        yield [$_ = new Collection\MutableVector([1, 2]), $_];
        yield [$_ = new Collection\Map([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = new Collection\MutableMap([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = $this->createStub(CollectionInterface::class), $_];
        yield [
            $_ = new class {
            },
            $_,
        ];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['hello'];
    }

    public function getToStringExamples(): iterable
    {
        yield [Type\object(), 'object'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\object(), Type\object());
    }
}
