<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ChrTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testChr(string $expected, int $value): void
    {
        static::assertSame($expected, Str\chr($value));
    }

    public static function provideData(): array
    {
        return [
            ['م', 1605],
            ['0', 48],
            ['&', 38],
            ['ا', 1575],
            ['A', 65],
        ];
    }

    public function testNegativeCodePointThrows(): void
    {
        $this->expectException(Str\Exception\OutOfBoundsException::class);

        Str\chr(-1);
    }

    public function testSurrogateThrows(): void
    {
        $this->expectException(Str\Exception\OutOfBoundsException::class);

        Str\chr(0xD800);
    }

    public function testAboveUnicodeMaxThrows(): void
    {
        $this->expectException(Str\Exception\OutOfBoundsException::class);

        Str\chr(0x11_0000);
    }
}
