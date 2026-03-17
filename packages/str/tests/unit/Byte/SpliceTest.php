<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class SpliceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSplice(
        string $expected,
        string $string,
        string $replacement,
        int $offset,
        null|int $length = null,
    ): void {
        static::assertSame($expected, Byte\splice($string, $replacement, $offset, $length));
    }

    public static function provideData(): array
    {
        return [
            ['', '', '', 0, null],
            ['hello darkness', 'hello world', 'darkness', 6, null],
            ['hello cruel world', 'hello world', ' cruel ', 5, 1],
            ['hello cruel world', 'hello world', ' cruel ', -6, 1],
            ['hello cruel world', 'hello world', ' cruel', 5, 0],
            ['hello darkness', 'hello ', 'darkness', 6, null],
            ['hello darkness', 'hello world', 'darkness', 6, 100],
            ['hello darkness', 'hello world', 'darkness', 6, 11],
            [
                'People linked by destiny will always find each other.',
                'People linked by destiny will find each other.',
                ' always ',
                29,
                1,
            ],
        ];
    }
}
