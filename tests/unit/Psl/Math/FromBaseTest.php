<?php

declare(strict_types=1);

namespace Psl\Tests\Math;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Math;

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
                2
            ],

            [
                2014587925987,
                'pphlmw9v',
                36
            ],

            [
                Math\INT32_MAX,
                'zik0zj',
                36
            ],

            [
                15,
                'F',
                16
            ]
        ];
    }

    public function testInvalidDigitThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid digit Z in base 16');

        Math\from_base('Z', 16);
    }

    public function testSpecialCharThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid digit * in base 16');

        Math\from_base('*', 16);
    }

    public function testEmptyValueThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Unexpected empty string, expected number in base 16');

        Math\from_base('', 16);
    }

    public function testInvalidFromBaseThrows(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected $from_base to be between 2 and 36, got 64');

        Math\from_base('z', 64);
    }
}
