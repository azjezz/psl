<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class ReplaceCiTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReplaceCi(string $expected, string $haystack, string $needle, string $replacement): void
    {
        static::assertSame($expected, Byte\replace_ci($haystack, $needle, $replacement));
    }

    public static function provideData(): array
    {
        return [
            ['Hello, World!', 'Hello, you!', 'You', 'World'],
            ['Hello, World!', 'Hello, You!', 'You', 'World'],
            ['مرحبا بكم',     'مرحبا سيف',   'سيف', 'بكم'],
            ['foo',           'foo',         'bar', 'baz'],
        ];
    }
}
