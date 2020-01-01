<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class CapitalizeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCapitalize(string $expected, string $value): void
    {
        self::assertSame($expected, Byte\capitalize($value));
    }

    public function provideData(): array
    {
        return [
            ['', ''],
            ['Hello', 'hello', ],
            ['Hello, world', 'hello, world'],
            ['Alpha', 'Alpha', ],
            ['Héllö, wôrld!', 'héllö, wôrld!'],
            ['ḫéllö, wôrld!', 'ḫéllö, wôrld!'],
            ['ßoo', 'ßoo'],
        ];
    }
}
