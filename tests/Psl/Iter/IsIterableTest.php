<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class IsIterableTest extends TestCase
{
    /**
     * @dataProvider provideIsIterableData
     */
    public function testIsIterable($value, bool $expected): void
    {
        self::assertSame($expected, Iter\is_iterable($value));
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
        yield [(fn () => yield false)(), true];
    }
}
