<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class CapitalizeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCapitalize(string $expected, string $value): void
    {
        self::assertSame($expected, Str\capitalize($value));
    }

    public function provideData(): array
    {
        return [
            ['Hello', 'hello', ],
            ['Hello, world', 'hello, world'],
            ['Alpha', 'Alpha', ],
            ['مرحبا بكم', 'مرحبا بكم', ],
        ];
    }
}
