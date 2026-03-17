<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class ClampTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testClamp(int|float $expected, int|float $number, int|float $min, int|float $max): void
    {
        static::assertSame($expected, Math\clamp($number, $min, $max));
    }

    public function testInvalidMinMax(): void
    {
        $this->expectException(Math\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected $min to be lower or equal to $max.');

        Math\clamp(10, 20, 10);
    }

    public static function provideData(): array
    {
        return [
            [
                'expected' => 10,
                'number' => 10,
                'min' => 2,
                'max' => 20,
            ],
            [
                'expected' => 10,
                'number' => 20,
                'min' => 1,
                'max' => 10,
            ],
            [
                'expected' => 10,
                'number' => 5,
                'min' => 10,
                'max' => 20,
            ],
            [
                'expected' => 10,
                'number' => 10,
                'min' => 10,
                'max' => 20,
            ],
            [
                'expected' => 10,
                'number' => 10,
                'min' => 1,
                'max' => 10,
            ],
            [
                'expected' => 10,
                'number' => 20,
                'min' => 10,
                'max' => 10,
            ],
            [
                'expected' => 10.0,
                'number' => 10.0,
                'min' => 2.0,
                'max' => 20.0,
            ],
        ];
    }
}
