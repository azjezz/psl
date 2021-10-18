<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Grapheme;

use PHPUnit\Framework\TestCase;
use Psl;

class ReverseTest extends TestCase
{
    public function provideData(): array
    {
        return [
            ['Hello World 👩🏽‍❤️‍👨🏼', '👩🏽‍❤️‍👨🏼 dlroW olleH'],
            ['👩‍👩‍👦👩🏽‍❤️‍👨🏼👩🏽‍🏫', '👩🏽‍🏫👩🏽‍❤️‍👨🏼👩‍👩‍👦'],
            ['某物 🖖🏿', '🖖🏿 物某' ],
            ['👲🏻 что-то', 'от-отч 👲🏻'],
            ['🙂👨🏼‍🎤😟', '😟👨🏼‍🎤🙂'],
            ['مرحبا👲🏻👨🏼‍🎤', '👨🏼‍🎤👲🏻ابحرم'],
        ];
    }

    /**
     * @dataProvider provideData
     */
    public function testReverse(string $string, string $expected): void
    {
        static::assertSame(Psl\Str\Grapheme\reverse($string), $expected);
    }

    public function testFailReverse(): void
    {
        $string = Psl\Str\Byte\slice('🐶🐶🐶', 0, 5);

        $this->expectException(Psl\Exception\InvalidArgumentException::class);
        Psl\Str\Grapheme\reverse($string);
    }
}
