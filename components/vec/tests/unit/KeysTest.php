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
        static::assertSame($expected, Vec\keys($iterable));
    }

    public static function provideData(): iterable
    {
        yield [[0, 1, 2, 3], [1, 2, 3, 4]];
        yield [[0, 1, 2, 3], Iter\to_iterator([1, 2, 3, 4])];
        yield [[0, 1, 2, 3], Vec\range(1, 4)];
        yield [[0, 1, 2, 3, 4], Vec\range(4, 8)];
        yield [[], []];
        yield [[0], [null]];
        yield [[0, 1], [null, null]];
    }
}
