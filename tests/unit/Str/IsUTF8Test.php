<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class IsUTF8Test extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIsUTF8Test(bool $expected, string $string): void
    {
        static::assertSame($expected, Str\is_utf8($string));
    }

    public function provideData(): array
    {
        return [
            [true, 'hello'],
            [true, '🐘'],
            [true, "\xc3\xb1"], // valid 2 octet sequence
            [true, "\xe2\x82\xa1"], // valid 3 octet sequence
            [true, "\xf0\x90\x8c\xbc"], // valid 4 octet sequence
            [false, "\xc3\x28"], // invalid 2 octet sequence
            [false, "\xa0\xa1"], // invalid sequence identifier
            [false, "\xe2\x28\xa1"], // invalid 3 octet sequence (in 2nd octet)
            [false, "\xe2\x82\x28"], // invalid 3 octet sequence (in 3rd octet)
            [false, "\xf0\x28\x8c\xbc"], // invalid 4 octet sequence (in 2nd octet)
            [false, "\xf0\x90\x28\xbc"], // invalid 4 octet sequence (in 3rd octet)
            [false, "\xf0\x28\x8c\x28"], // invalid 4 octet sequence (in 4th octet)
            [false, "\xf8\xa1\xa1\xa1\xa1"], // valid 5 octet sequence (but not unicode!)
            [false, "\xfc\xa1\xa1\xa1\xa1\xa1"], // valid 6 octet Sequence (but not unicode!)
        ];
    }
}
