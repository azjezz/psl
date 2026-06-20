<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class KeysTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testKeys(array $expected, iterable $iterable): void
    {
        static::assertSame($expected, Vec\keys::<int, int>($iterable));
    }

    public static function provideData(): iterable
    {
        yield [[0, 1, 2, 3], [1, 2, 3, 4]];
        yield [[0, 1, 2, 3], Iter\to_iterator::<int, int>([1, 2, 3, 4])];
        yield [[0, 1, 2, 3], Vec\range::<int>(1, 4)];
        yield [[0, 1, 2, 3, 4], Vec\range::<int>(4, 8)];
        yield [[], []];
        yield [[0], [null]];
        yield [[0, 1], [null, null]];
    }
}
