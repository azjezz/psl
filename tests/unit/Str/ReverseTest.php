<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ReverseTest extends TestCase
{
    public function provideData(): array
    {
        return [
            ['Hello World', 'dlroW olleH'],
            ['héllö wôrld', 'dlrôw ölléh'],
            ['Iñigo Montoya', 'ayotnoM ogiñI'],
            ['某物', '物某'],
            ['что-то', 'от-отч'],
            ['🙂😟', '😟🙂'],
            ['مرحبا', 'ابحرم'],
        ];
    }

    /**
     * @dataProvider provideData
     */
    public function testReverse(string $string, string $expected): void
    {
        static::assertSame(Str\reverse($string), $expected);
    }
}
