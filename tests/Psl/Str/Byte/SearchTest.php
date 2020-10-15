<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class SearchTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testSearch(?int $expected, string $haystack, string $needle, int $offset = 0): void
    {
        static::assertSame($expected, Byte\search($haystack, $needle, $offset));
    }

    public function provideData(): array
    {
        return [
            [null, 'Hello, you!', 'You', ],
            [7, 'Hello, You!', 'You', ],
            [null, 'Ho! Ho! Ho!', 'ho', ],
            [0, 'Ho! Ho! Ho!', 'Ho', ],
            [7, 'Hello, You!', 'You', 5],
            [null, 'Hello, World!', 'You', 5],
            [null, 'foo', 'bar', 2],
        ];
    }
}
