<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class ReplaceCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReplaceCi(string $expected, string $haystack, string $needle, string $replacement): void
    {
        self::assertSame($expected, Byte\replace_ci($haystack, $needle, $replacement));
    }

    public function provideData(): array
    {
        return [
            ['Hello, World!', 'Hello, you!', 'You', 'World', ],
            ['Hello, World!', 'Hello, You!', 'You', 'World', ],
            ['مرحبا بكم', 'مرحبا سيف', 'سيف', 'بكم'],
            ['foo', 'foo', 'bar', 'baz'],
        ];
    }
}
