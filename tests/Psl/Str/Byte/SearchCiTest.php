<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class SearchCiTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testSearchCi(?int $expected, string $haystack, string $needle, int $offset = 0): void
    {
        static::assertSame($expected, Byte\search_ci($haystack, $needle, $offset));
    }

    public function provideData(): array
    {
        return [
            [7, 'Hello, you!', 'You', ],
            [7, 'Hello, You!', 'You', ],
            [0, 'Ho! Ho! Ho!', 'ho', ],
            [0, 'Ho! Ho! Ho!', 'Ho', ],
            [7, 'Hello, You!', 'You', 5],
            [null, 'Hello, World!', 'You', 5],
            [null, 'foo', 'bar', 2],
        ];
    }
}
