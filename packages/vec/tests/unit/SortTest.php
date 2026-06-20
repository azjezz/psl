<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class SortTest extends TestCase
{
    /**
     * @param list<mixed> $expected
     * @param (callable(mixed, mixed): int)|null $comparator
     */
    #[DataProvider('provideData')]
    public function testSort(array $expected, array $array, null|callable $comparator = null): void
    {
        static::assertSame($expected, Vec\sort::<mixed>($array, $comparator));
    }

    public static function provideData(): array
    {
        return [
            [
                ['a', 'b', 'c'],
                ['c', 'a', 'b'],
            ],
            [
                [8, 9, 10],
                [8, 9, 10],
                /**
                 * @psalm-pure
                 */
                static fn(int $a, int $b): int => $a <=> $b ? -1 : 1,
            ],
            [
                ['bar', 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }
}
