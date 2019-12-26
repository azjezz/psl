<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class EndsWithCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEndsWithCi(bool $expected, string $haystack, string $suffix): void
    {
        self::assertSame($expected, Byte\ends_with_ci($haystack, $suffix));
    }

    public function provideData(): array
    {
        return [
            [true, 'Hello', 'Hello'],
            [false, 'Hello, WorlḐ', 'worlḑ', ],
            [true, 'Hello, Worlḑ', 'worlḑ', ],
            [false, 'T U N I S I A', 'e', ],
            [true, 'تونس', 'س'],
            [false, 'Hello, World', '', ],
            [false, 'hello, world', 'hey', ],
            [false, 'hello, worlḑ', 'hello cruel worḑ'],
            [true, 'azjezz', 'z', ],
            [true, 'مرحبا بكم', 'بكم', ],
        ];
    }
}
