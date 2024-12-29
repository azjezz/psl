<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Exception;
use Psl\Str;

final class GroupByTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testGroupBy(array $expected, array $values, callable $callable): void
    {
        static::assertSame($expected, Dict\group_by($values, $callable));
    }

    public function provideData(): array
    {
        return [
            [
                [7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7, 8, 9, 10]],
                [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn($i) => $i < 2 ? null : ($i >= 7 ? 12 : ($i + 5)),
            ],
            [
                [7 => [2], 8 => [3], 9 => [4], 10 => [5], 11 => [6], 12 => [7], 13 => [8], 14 => [9], 15 => [10]],
                [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn($i) => $i < 2 ? null : ($i + 5),
            ],
            [
                ['username' => ['@azjezz', '@veewee', '@ocramius'], 'name' => ['Saif', 'Toon', 'Marco']],
                ['@azjezz', 'Saif', '@veewee', 'Toon', '@ocramius', 'Marco'],
                static fn($name) => Str\starts_with($name, '@') ? 'username' : 'name',
            ],
        ];
    }

    public function testGroupByThrowsWhenKeyFunReturnsNonArrayKey(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage(
            'Expected $key_func to return a value of type array-key, value of type (object) returned.',
        );

        Dict\group_by([0, 1, 2, 3, 4, 5], static fn($x) => new Collection\Vector([$x, $x]));
    }
}
