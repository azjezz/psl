<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class CapitalizeTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testCapitalize(string $expected, string $value): void
    {
        static::assertSame($expected, Byte\capitalize($value));
    }

    public static function provideData(): array
    {
        return [
            ['',              ''],
            ['Hello',         'hello'],
            ['Hello, world',  'hello, world'],
            ['Alpha',         'Alpha'],
            ['Héllö, wôrld!', 'héllö, wôrld!'],
            ['ßoo',           'ßoo'],
        ];
    }
}
