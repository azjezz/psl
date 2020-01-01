<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class ReverseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReverse(string $expected, string $input): void
    {
        self::assertSame($expected, Byte\reverse($input));
    }

    public function provideData(): array
    {
        return [
            ['oof', 'foo'],
            ['', ''],
        ];
    }
}
