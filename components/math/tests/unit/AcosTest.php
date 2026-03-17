<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Math;

final class AcosTest extends TestCase
{
    use FloatAsserts;

    #[DataProvider('provideData')]
    public function testAcos(float $expected, float $number): void
    {
        static::assertFloatEquals($expected, Math\acos($number));
    }

    public static function provideData(): array
    {
        return [
            [0.0,                     1.0],
            [1.266_103_672_779_499_2, 0.3],
            [1.047_197_551_196_597_9, 0.5],
        ];
    }
}
