<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;
use Psl\Exception;
use Psl\Iter;
use Psl\Str;

/**
 * @covers \Psl\Arr\group_by
 */
class GroupByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testGroupBy(array $expected, iterable $values, callable $callable): void
    {
        self::assertSame($expected, Arr\group_by($values, $callable));
    }

    public function provideData(): array
    {
        return [
            [
                [7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7, 8, 9, 10]],
                Iter\range(0, 10),
                fn ($i) => $i < 2 ? null : (($i >= 7) ? 12 : $i + 5),
            ],

            [
                [7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7], 13 => [8], 14 => [9], 15 => [10]],
                Iter\range(0, 10),
                fn ($i) => $i < 2 ? null : $i + 5,
            ],

            [
                ['username' => ['@azjezz', '@fabpot', '@blacksun'], 'name' => ['Saif Eddin', 'Fabien', 'Gabrielle']],
                new Collection\Vector(['@azjezz', 'Saif Eddin', '@fabpot', 'Fabien', '@blacksun', 'Gabrielle']),
                fn ($name) => Str\starts_with($name, '@') ? 'username' : 'name',
            ],
        ];
    }

    public function testGroupByThrowsWhenKeyFunReturnsNonArrayKey(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected $key_func to return a value of type array-key, value of type (object) returned.');

        Arr\group_by(
            Iter\range(0, 5),
            fn ($x) => new Collection\Pair($x, $x)
        );
    }
}
