<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class CapitalizeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCapitalize(string $expected, string $value): void
    {
        static::assertSame($expected, Byte\capitalize($value));
    }

    public function provideData(): array
    {
        return [
            ['', ''],
            ['Hello', 'hello'],
            ['Hello, world', 'hello, world'],
            ['Alpha', 'Alpha'],
            ['Héllö, wôrld!', 'héllö, wôrld!'],
            ['ßoo', 'ßoo'],
        ];
    }
}
