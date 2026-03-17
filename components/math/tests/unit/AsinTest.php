<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AsinTest extends TestCase
{
    use FloatAsserts;

    #[DataProvider('provideData')]
    public function testAsin(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\asin($number));
    }

    public static function provideData(): array
    {
        return [
            [0.523_598_775_598_298_9,  0.5],
            [0.927_295_218_001_612_3,  0.8],
            [0.0,                      0.0],
            [0.411_516_846_067_488_06, 0.4],
        ];
    }
}
