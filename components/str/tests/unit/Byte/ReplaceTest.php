<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class ReplaceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReplace(string $expected, string $haystack, string $needle, string $replacement): void
    {
        static::assertSame($expected, Byte\replace($haystack, $needle, $replacement));
    }

    public static function provideData(): array
    {
        return [
            ['Hello, you!',   'Hello, you!', 'You', 'World'],
            ['Hello, World!', 'Hello, You!', 'You', 'World'],
            ['مرحبا بكم',     'مرحبا سيف',   'سيف', 'بكم'],
            ['foo',           'foo',         'bar', 'baz'],
        ];
    }
}
