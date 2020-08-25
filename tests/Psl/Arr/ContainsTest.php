<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Collection;

class ContainsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testContains(bool $expected, array $array, $value): void
    {
        static::assertSame($expected, Arr\contains($array, $value));
    }

    public function provideData(): array
    {
        return [
            [true, [1, 2], 1],
            [false, ['0'], 0],
            [false, [], 'hello'],
            [false, ['HELLO'], 'hello'],
        ];
    }
}
