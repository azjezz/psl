<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

final class BaseConvertTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testBaseConvert(string $expected, string $value, int $from, int $to): void
    {
        static::assertSame($expected, Math\base_convert($value, $from, $to));
    }

    public function provideData(): array
    {
        return [
            [
                '2',
                '10',
                2,
                16,
            ],
            [
                '2',
                '10',
                2,
                10,
            ],
            [
                'f',
                '15',
                10,
                16,
            ],
            [
                '10',
                '2',
                16,
                2,
            ],
            [
                '1010101111001',
                '5497',
                10,
                2,
            ],
            [
                '48p',
                '1010101111001',
                2,
                36,
            ],
            [
                'pphlmw9v',
                '2014587925987',
                10,
                36,
            ],
            [
                'zik0zj',
                (string) Math\INT32_MAX,
                10,
                36,
            ],
        ];
    }
}
