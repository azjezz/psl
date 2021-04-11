<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Exception;

final class FlipTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFlip(array $expected, $actual): void
    {
        static::assertSame($expected, Arr\flip($actual));
    }

    public function provideData(): array
    {
        return [
            [
                ['a' => 'b', 'b' => 'c', 'c' => 'd'],
                ['b' => 'a', 'c' => 'b', 'd' => 'c'],
            ],

            [
                [1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6, 8 => 7, 9 => 8, 10 => 9],
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            ],
        ];
    }

    public function testFlipThrowsForNonArrayKeyValues(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage(
            'Expected all values to be of type array-key, value of type (resource) provided.'
        );

        Arr\flip([STDOUT]);
    }
}
