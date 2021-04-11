<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Type;

final class IsIterableTest extends TestCase
{
    /**
     * @dataProvider provideIsIterableData
     */
    public function testIsIterable($value, bool $expected): void
    {
        static::assertSame($expected, Type\is_iterable($value));
    }

    public function provideIsIterableData(): iterable
    {
        yield [false, false];
        yield [true, false];
        yield [null, false];
        yield [[], true];
        yield [Iter\to_iterator([1, 2]), true];
        yield [$this, false];
        yield [STDIN, false];
        yield [(static fn () => yield false)(), true];
    }
}
