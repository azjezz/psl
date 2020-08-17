<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use Psl\Iter;
use PHPUnit\Framework\TestCase;

class IsIterableTest extends TestCase
{
    /**
     * @dataProvider provideIsIterableData
     */
    public function testIsIterable($expected, $value): void
    {
        self::assertSame($expected, Iter\is_iterable($value));
    }

    private function provideIsIterableData(): iterable
    {
        yield [false, false];
        yield [true, false];
        yield [null, false];
        yield [[], true];
        yield [Iter\to_iterator([1, 2]), true];
        yield [$this, false];
        yield [STDIN, false];
        yield [(fn() => yield false)(), true];
    }
}
