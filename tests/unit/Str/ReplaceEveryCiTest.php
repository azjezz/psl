<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ReplaceEveryCiTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReplaceEveryCi(string $expected, string $haystack, iterable $replacements): void
    {
        static::assertSame($expected, Str\replace_every_ci($haystack, $replacements));
    }

    public static function provideData(): array
    {
        return [
            [
                'Hello, World!',
                'Hello, you!',
                ['You' => 'World'],
            ],
            [
                'Hello, World!',
                'Hello, You!',
                ['You' => 'World'],
            ],
            [
                'مرحبا بكم',
                'مرحبا سيف',
                ['سيف' => 'بكم'],
            ],
            [
                'اهلا بكم',
                'مرحبا سيف',
                [
                    'سيف' => 'بكم',
                    'مرحبا' => 'اهلا',
                ],
            ],
            [
                'Foo',
                'Foo',
                ['bar' => 'baz'],
            ],
        ];
    }
}
