<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Collection;
use Psl\Collection\CollectionInterface;
use Psl\Type;

/**
 * @extends TypeTestCase<Collection\CollectionInterface>
 */
final class InstanceOfTypeTest extends TypeTestCase<object>
{
    #[Override]
    public static function getType(): Type\TypeInterface<object>
    {
        return Type\instance_of::<Collection\CollectionInterface>(Collection\CollectionInterface::class);
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [$_ = new Collection\Vector::<int>([1, 2]), $_];
        yield [$_ = new Collection\MutableVector::<int>([1, 2]), $_];
        yield [$_ = new Collection\Map::<int, string>([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = new Collection\MutableMap::<int, string>([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = new Collection\Set::<int>([]), $_];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['hello'];
        yield [static::stringable('foo')];
        yield [new class {}];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [Type\instance_of::<Collection\MapInterface>(Collection\MapInterface::class), Collection\MapInterface::class];
        yield [Type\instance_of::<Collection\VectorInterface>(Collection\VectorInterface::class), Collection\VectorInterface::class];
        yield [Type\instance_of::<Collection\Vector>(Collection\Vector::class), Collection\Vector::class];
        yield [Type\instance_of::<Collection\Map>(Collection\Map::class), Collection\Map::class];
    }
}
