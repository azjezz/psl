<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ChunkTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testCapitalize(array $expected, string $value, int $chunk_size = 1): void
    {
        static::assertSame($expected, Str\chunk($value, $chunk_size));
    }

    public static function provideData(): array
    {
        return [
            [['h', 'e', 'l', 'l', 'o'], 'hello'],
            [['h', 'e', 'l', 'l', 'o', ',', ' ', 'w', 'o', 'r', 'l', 'd'], 'hello, world'],
            [['Al', 'ph', 'a ', ' '], 'Alpha  ', 2],
            [['م', 'ر', 'ح', 'ب', 'ا', ' ', 'ب', 'ك', 'م'], 'مرحبا بكم'],
            [['مرحبا بكم'], 'مرحبا بكم', 9],
            [[], ''],
        ];
    }
}
