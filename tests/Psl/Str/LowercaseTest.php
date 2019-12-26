<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class LowercaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLowercase(string $expected, string $str): void
    {
        self::assertSame($expected, Str\lowercase($str));
    }

    public function provideData(): array
    {
        return [
            ['hello', 'hello'],
            ['hello', 'Hello'],
            ['سيف', 'سيف'],
            ['1337', '1337']
        ];
    }
}
