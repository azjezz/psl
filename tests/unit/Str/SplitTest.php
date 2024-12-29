<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class SplitTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSplit(array $expected, string $string, string $delimiter, null|int $length = null): void
    {
        static::assertSame($expected, Str\split($string, $delimiter, $length));
    }

    public function provideData(): array
    {
        return [
            [[], '', '', 1],
            [['Hello, World!'], 'Hello, World!', ' ', 1],
            [['Hello,', 'World!'], 'Hello, World!', ' ', 2],
            [['مرحبا', 'سيف'], 'مرحبا سيف', ' ', 3],
            [['اهلا', 'بكم'], 'اهلا بكم', ' ', 2],
            [['اهلا بكم'], 'اهلا بكم', '', 1],
            [['اهلا', 'بكم'], 'اهلا بكم', ' ', null],
            [['اهلا', 'بكم'], 'اهلا بكم', ' ', 100],
            [['h', 'é', 'l', 'l', 'ö', ' ', 'w', 'ôrld'], 'héllö wôrld', '', 8],
            [['h', 'é', 'l', 'l', 'ö', ' ', 'w', 'ô', 'r', 'l', 'd'], 'héllö wôrld', ''],
            [['fôo'], 'fôo', 'bar', null],
            [['héll', ' w', 'rld'], 'héllô wôrld', 'ô', 3],
            [['héllö w', 'rld'], 'héllö wôrld', 'ô', 3],
        ];
    }
}
