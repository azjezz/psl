<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class ChunkTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCapitalize(array $expected, string $value, int $chunk_size = 1): void
    {
        self::assertSame($expected, Byte\chunk($value, $chunk_size));
    }

    public function provideData(): array
    {
        return [
            [['h', 'e', 'l', 'l', 'o'], 'hello', ],
            [['h', 'e', 'l', 'l', 'o', ',', ' ', 'w', 'o', 'r', 'l', 'd'], 'hello, world', ],
            [['Al', 'ph', 'a ', ' '], 'Alpha  ', 2, ],
            [['م', 'ر', 'ح', 'ب', 'ا'], 'مرحبا', 2],
            [[], ''],
        ];
    }
}
