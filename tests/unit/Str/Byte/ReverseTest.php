<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class ReverseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReverse(string $expected, string $input): void
    {
        static::assertSame($expected, Byte\reverse($input));
    }

    public function provideData(): array
    {
        return [
            ['oof', 'foo'],
            ['', ''],
        ];
    }
}
