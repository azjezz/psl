<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Math;

class LogTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLog(float $expected, float $number, ?float $base = null): void
    {
        self::assertSame($expected, Math\log($number, $base));
    }

    public function provideData(): array
    {
        return [
            [
                1.6863989535702288,
                5.4,
                null
            ],

            [
                0.6574784600188808,
                5.4,
                13
            ],

            [
                1.7323937598229686,
                54.0,
                10
            ],

            [
                0,
                1,
                null
            ],
        ];
    }

    public function testNegativeInputThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-negative number.');

        Math\log(-45);
    }

    public function testNonPositiveBaseThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-negative base.');

        Math\log(4.4, 0.0);
    }

    public function testBaseOneThrowsForUndefinedLogarithm(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Logarithm undefined for base 1.');

        Math\log(4.4, 1.0);
    }
}
