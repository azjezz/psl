<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class EndsWithCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEndsWithCi(bool $expected, string $haystack, string $suffix): void
    {
        self::assertSame($expected, Str\ends_with_ci($haystack, $suffix));
    }

    public function provideData(): array
    {
        return [
            [true, 'Hello, World', 'world', ],
            [false, 'T U N I S I A', 'e', ],
            [true, 'تونس', 'س'],
            [true, 'Hello, World', '', ],
            [false, 'hello, world', 'hey', ],
            [true, 'azjezz', 'z', ],
            [true, 'مرحبا بكم', 'بكم', ],
        ];
    }
}
