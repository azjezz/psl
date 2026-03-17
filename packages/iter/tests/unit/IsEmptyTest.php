<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class IsEmptyTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testIsEmpty(bool $expected, iterable $iterable): void
    {
        static::assertSame($expected, Iter\is_empty($iterable));
    }

    public static function provideData(): iterable
    {
        yield [true, []];
        yield [true, Iter\to_iterator([])];
        yield [true, (static fn(): iterable => yield from [])()];

        yield [false, [null]];
        yield [false, [false]];
        yield [false, ['hello', 'world']];
    }
}
