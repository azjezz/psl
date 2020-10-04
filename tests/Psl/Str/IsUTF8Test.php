<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class IsUTF8Test extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIsUTF8Test(?string $expected, string $string): bool
    {
        self::assertSame($expected, Str\is_utf8($string));
    }

    public function provideData(): array
    {
        return [
            ['1', 'åäö'],
            ['1', '🐘'],
        ];
    }
}
