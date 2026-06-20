<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Collection;
use Psl\Type;

final class ObjectTypeTest extends TypeTestCase<object>
{
    #[Override]
    public static function getType(): Type\TypeInterface<object>
    {
        return Type\object();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [$_ = new Collection\Vector::<int>([1, 2]), $_];
        yield [$_ = new Collection\MutableVector::<int>([1, 2]), $_];
        yield [$_ = new Collection\Map::<int, string>([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = new Collection\MutableMap::<int, string>([1 => 'hey', 2 => 'hello']), $_];
        yield [$_ = new Collection\Set::<int>([]), $_];
        yield [
            $_ = new class {},
            $_,
        ];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield [STDIN];
        yield ['hello'];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [Type\object(), 'object'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\object(), Type\object());
    }
}
