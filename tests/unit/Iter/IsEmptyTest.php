<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class IsEmptyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIsEmpty(bool $expected, iterable $iterable): void
    {
        static::assertSame($expected, Iter\is_empty($iterable));
    }

    public function provideData(): iterable
    {
        yield [true, []];
        yield [true, Iter\to_iterator([])];
        yield [true, (static fn() => yield from [])()];

        yield [false, [null]];
        yield [false, [false]];
        yield [false, ['hello', 'world']];
    }
}
