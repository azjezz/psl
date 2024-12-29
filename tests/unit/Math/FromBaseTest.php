<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Str;

final class FromBaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFromBase(int $expected, string $value, int $from_base): void
    {
        static::assertSame($expected, Math\from_base($value, $from_base));
    }

    public function provideData(): array
    {
        return [
            [
                5497,
                '1010101111001',
                2,
            ],
            [
                2014587925987,
                'pphlmw9v',
                36,
            ],
            [
                Math\INT32_MAX,
                'zik0zj',
                36,
            ],
            [
                15,
                'F',
                16,
            ],
        ];
    }

    public function testInvalidDigitThrows(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid digit Z in base 16');

        Math\from_base('Z', 16);
    }

    public function testSpecialCharThrows(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid digit * in base 16');

        Math\from_base('*', 16);
    }

    public function testThrowsForOverflow(): void
    {
        $number = Str\repeat('A', 100);

        $this->expectException(Math\Exception\OverflowException::class);
        $this->expectExceptionMessage('Unexpected integer overflow parsing ' . $number . ' from base 32');

        Math\from_base($number, 32);
    }
}
