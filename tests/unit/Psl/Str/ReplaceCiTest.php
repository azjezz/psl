<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ReplaceCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReplaceCi(string $expected, string $haystack, string $needle, string $replacement): void
    {
        static::assertSame($expected, Str\replace_ci($haystack, $needle, $replacement));
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
