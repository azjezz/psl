<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class SpliceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSplice(
        string $expected,
        string $string,
        string $replacement,
        int $offset,
        null|int $length = null,
    ): void {
        static::assertSame($expected, Str\splice($string, $replacement, $offset, $length));
    }

    public function provideData(): array
    {
        return [
            ['', '', '', 0, null],
            ['héllö darkness', 'héllö wôrld', 'darkness', 6, null],
            ['héllö crüel wôrld', 'héllö wôrld', ' crüel ', 5, 1],
            ['héllö crüel wôrld', 'héllö wôrld', ' crüel ', -6, 1],
            ['héllö crüel wôrld', 'héllö wôrld', ' crüel', 5, 0],
            ['héllö darkness', 'héllö ', 'darkness', 6, null],
            ['héllö darkness', 'héllö wôrld', 'darkness', 6, 100],
            ['héllö darkness', 'héllö wôrld', 'darkness', 6, 11],
            [
                'Peôple linkéd by déstiny wȋll ȃlways find each öther.',
                'Peôple linkéd by déstiny wȋll find each öther.',
                ' ȃlways ',
                29,
                1,
            ],
        ];
    }
}
