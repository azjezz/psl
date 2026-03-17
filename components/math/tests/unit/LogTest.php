<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class LogTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testLog(float $expected, float $number, null|float $base = null): void
    {
        static::assertSame($expected, Math\log($number, $base));
    }

    public static function provideData(): array
    {
        return [
            [1.686_398_953_570_228_8, 5.4,  null],
            [0.657_478_460_018_880_8, 5.4,  13],
            [1.732_393_759_822_968_6, 54.0, 10],
            [0,                       1,    null],
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
