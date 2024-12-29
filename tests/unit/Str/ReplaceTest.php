<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ReplaceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReplace(string $expected, string $haystack, string $needle, string $replacement): void
    {
        static::assertSame($expected, Str\replace($haystack, $needle, $replacement));
    }

    public function provideData(): array
    {
        return [
            ['Hello, you!', 'Hello, you!', 'You', 'World'],
            ['Hello, World!', 'Hello, You!', 'You', 'World'],
            ['مرحبا بكم', 'مرحبا سيف', 'سيف', 'بكم'],
            ['foo', 'foo', 'bar', 'baz'],
        ];
    }
}
