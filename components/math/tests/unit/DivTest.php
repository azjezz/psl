<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class DivTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testDiv(int $expected, int $numerator, int $denominator): void
    {
        static::assertSame($expected, Math\div($numerator, $denominator));
    }

    public function testDivByZero(): void
    {
        $this->expectException(Math\Exception\DivisionByZeroException::class);
        $this->expectExceptionMessage('Division by zero.');

        Math\div(10, 0);
    }

    public function testDivInt64MinByMinusOne(): void
    {
        $this->expectException(Math\Exception\ArithmeticException::class);
        $this->expectExceptionMessage('Division of Math\INT64_MIN by -1 is not an integer.');

        Math\div(Math\INT64_MIN, -1);
    }

    public static function provideData(): array
    {
        return [
            [
                2,
                5,
                2,
            ],
            [
                5,
                10,
                2,
            ],
            [
                0,
                15,
                20,
            ],
            [
                1,
                10,
                10,
            ],
        ];
    }
}
