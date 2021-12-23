<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class UppercaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testUppercase(string $expected, string $str): void
    {
        static::assertSame($expected, Byte\uppercase($str));
    }

    public function provideData(): array
    {
        return [
            ['HELLO', 'hello'],
            ['HELLO', 'helLO'],
            ['HELLO', 'Hello'],
            ['1337', '1337'],
        ];
    }
}
