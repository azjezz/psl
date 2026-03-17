<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class SqrtTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSqrt(float $expected, float $number): void
    {
        static::assertSame($expected, Math\sqrt($number));
    }

    public static function provideData(): array
    {
        return [
            [2.236_067_977_499_79,    5.0],
            [2.190_890_230_020_664_3, 4.8],
            [0.632_455_532_033_675_9, 0.4],
            [2.549_509_756_796_392_2, 6.5],
            [1.414_213_562_373_095_1, 2],
            [1,                       1],
        ];
    }

    public function testSqrtThrowsForNegativeNumber(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$number must be a non-negative number.');

        Math\sqrt(-1.0);
    }
}
