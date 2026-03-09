<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class Rot13Test extends TestCase
{
    #[DataProvider('provideData')]
    public function testWords(string $expected, string $string): void
    {
        static::assertSame($expected, Byte\rot13($string));
    }

    public static function provideData(): array
    {
        return [
            ['',              ''],
            ['Uryyb',         'Hello'],
            ['Uryyb, Jbeyq!', 'Hello, World!'],
        ];
    }
}
