<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class LowercaseTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testLowercase(string $expected, string $str): void
    {
        static::assertSame($expected, Byte\lowercase($str));
    }

    public static function provideData(): array
    {
        return [
            ['hello', 'hello'],
            ['hello', 'Hello'],
            ['1337', '1337'],
            ['1337', '1337'],
        ];
    }
}
