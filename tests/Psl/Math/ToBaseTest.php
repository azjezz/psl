<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Math;

class ToBaseTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testFromBase(string $expected, int $value, int $to_base): void
    {
        self::assertSame($expected, Math\to_base($value, $to_base));
    }

    public function provideData(): array
    {
        return [
            [
                '1010101111001',
                5497,
                2
            ],

            [
                'pphlmw9v',
                2014587925987,
                36
            ],

            [
                'zik0zj',
                Math\INT32_MAX,
                36
            ],

            [
                'f',
                15,
                16
            ]
        ];
    }

    public function testNegativeValueThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected non-negative base conversion input, got -5');

        Math\to_base(-5, 16);
    }

    public function testInvalidToBaseThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected $to_base to be between 2 and 36, got 64');

        Math\to_base(1, 64);
    }
}
