<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Grapheme;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;
use Psl\Str\Exception;
use Psl\Str\Grapheme;

class ReverseTest extends TestCase
{
    public function provideData(): array
    {
        return [
            ['Hello World 👩🏽‍❤️‍👨🏼', '👩🏽‍❤️‍👨🏼 dlroW olleH'],
            [
                '👩‍👩‍👦👩🏽‍❤️‍👨🏼👩🏽‍🏫',
                '👩🏽‍🏫👩🏽‍❤️‍👨🏼👩‍👩‍👦',
            ],
            ['某物 🖖🏿', '🖖🏿 物某'],
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
        static::assertSame(Grapheme\reverse($string), $expected);
    }

    public function testFailReverse(): void
    {
        $string = Byte\slice('🐶🐶🐶', 0, 5);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$string is node made of grapheme clusters.');

        Grapheme\reverse($string);
    }
}
