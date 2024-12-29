<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class LogTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLog(float $expected, float $number, null|float $base = null): void
    {
        static::assertSame($expected, Math\log($number, $base));
    }

    public function provideData(): array
    {
        return [
            [
                1.6863989535702288,
                5.4,
                null,
            ],
            [
                0.6574784600188808,
                5.4,
                13,
            ],
            [
                1.7323937598229686,
                54.0,
                10,
            ],
            [
                0,
                1,
                null,
            ],
        ];
    }

    public function testNegativeInputThrows(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$number must be positive.');

        Math\log(-45);
    }

    public function testNonPositiveBaseThrows(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$base must be positive.');

        Math\log(4.4, 0.0);
    }

    public function testBaseOneThrowsForUndefinedLogarithm(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Logarithm undefined for $base of 1.0.');

        Math\log(4.4, 1.0);
    }
}
