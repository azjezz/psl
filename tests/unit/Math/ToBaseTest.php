<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class ToBaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFromBase(string $expected, int $value, int $to_base): void
    {
        static::assertSame($expected, Math\to_base($value, $to_base));
    }

    public function provideData(): array
    {
        return [
            [
                '1010101111001',
                5497,
                2,
            ],
            [
                'pphlmw9v',
                2014587925987,
                36,
            ],
            [
                'zik0zj',
                Math\INT32_MAX,
                36,
            ],
            [
                'f',
                15,
                16,
            ],
        ];
    }
}
