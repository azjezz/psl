<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class ReplaceEveryTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testReplaceEvery(string $expected, string $haystack, iterable $replacements): void
    {
        self::assertSame($expected, Str\replace_every($haystack, $replacements));
    }

    public function provideData(): array
    {
        return [
            [
                'Hello, you!',
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
                ['سيف' => 'بكم', 'مرحبا' => 'اهلا'],
            ],
            [
                'Foo',
                'Foo',
                ['bar' => 'baz']
            ]
        ];
    }
}
