<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class LowercaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLowercase(string $expected, string $str): void
    {
        self::assertSame($expected, Byte\lowercase($str));
    }

    public function provideData(): array
    {
        return [
            ['hello', 'hello'],
            ['hello', 'Hello'],
            ['1337', '1337'],
            ['1337', '1337'],
        ];
    }
}
