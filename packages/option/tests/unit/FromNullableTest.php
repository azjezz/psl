<?php

declare(strict_types=1);

namespace Psl\Option\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Option;
use stdClass;

final class FromNullableTest extends TestCase
{
    public function testIsSome(): void
    {
        static::assertTrue(Option\from_nullable::<int>(1)->isSome());
        static::assertTrue(Option\from_nullable::<float>(1.1)->isSome());
        static::assertTrue(Option\from_nullable::<bool>(true)->isSome());
        static::assertTrue(Option\from_nullable::<bool>(false)->isSome());
        static::assertTrue(Option\from_nullable::<string>('hello')->isSome());
        static::assertTrue(Option\from_nullable::<array>([])->isSome());
        static::assertTrue(Option\from_nullable::<stdClass>(new stdClass())->isSome());
        static::assertTrue(Option\from_nullable::<\Closure>(static fn(): string => '')->isSome());
        static::assertTrue(
            Option\from_nullable::<\Closure>(static function (): iterable {
                yield 'hello';
            })->isSome(),
        );
    }

    public function testIsNone(): void
    {
        $option = Option\from_nullable::<null>(null);

        static::assertTrue($option->isNone());
    }
}
