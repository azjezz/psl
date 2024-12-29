<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Grapheme;

use PHPUnit\Framework\TestCase;
use Psl\Range;
use Psl\Str\Exception;
use Psl\Str\Grapheme;

final class RangeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testRange(string $expected, string $string, Range\RangeInterface $range): void
    {
        static::assertSame($expected, Grapheme\range($string, $range));
    }

    /**
     * @return list<{0: string, 1: string, 2: Range\RangeInterface}>
     */
    public function provideData(): array
    {
        return [
            ['', '', Range\between(0, 5, upper_inclusive: true)],
            ['Hello,', 'Hello, World!', Range\between(0, 5, upper_inclusive: true)],
            ['Hello', 'Hello, World!', Range\between(0, 5, upper_inclusive: false)],
            ['Hello, World!', 'Hello, World!', Range\from(0)],
            ['World!', 'Hello, World!', Range\between(7, 12, upper_inclusive: true)],
            ['World', 'Hello, World!', Range\between(7, 12, upper_inclusive: false)],
            ['سيف', 'مرحبا سيف', Range\between(6, 9, upper_inclusive: true)],
            ['اهلا', 'اهلا بكم', Range\between(0, 3, upper_inclusive: true)],
            ['اهلا', 'اهلا بكم', Range\between(0, 4, upper_inclusive: false)],
            [
                'destiny',
                'People linked by destiny will always find each other.',
                Range\between(17, 23, upper_inclusive: true),
            ],
            [
                'destiny',
                'People linked by destiny will always find each other.',
                Range\between(17, 24, upper_inclusive: false),
            ],
            ['lö ', 'héllö wôrld', Range\between(3, 5, upper_inclusive: true)],
            ['lö ', 'héllö wôrld', Range\between(3, 6, upper_inclusive: false)],
            ['lö wôrld', 'héllö wôrld', Range\from(3)],
            ['héll', 'héllö wôrld', Range\to(3, inclusive: true)],
            ['hél', 'héllö wôrld', Range\to(3, inclusive: false)],
            ['', 'lö wôrld', Range\between(3, 3)],
            ['', 'fôo', Range\between(3, 3)],
            ['', 'fôo', Range\between(3, 12)],
            ['fôo', 'fôo', Range\full()],
            [
                'he̡̙̬͎̿́̐̅̕͢l͕̮͕͈̜͐̈́̇̕͠ļ͚͉̗̘̽͑̿͑̚o̼̰̼͕̞̍̄̎̿̊,̻̰̻̘́̎͒̋͘͟ ̧̬̝͈̬̿͌̿̑̕ẉ̣̟͉̮͆̊̃͐̈́ờ̢̫͎͖̹͊́͐r̨̮͓͓̣̅̋͐͐͆ḻ̩̦͚̯͑̌̓̅͒d͇̯͔̼͍͛̾͛͡͝',
                'he̡̙̬͎̿́̐̅̕͢l͕̮͕͈̜͐̈́̇̕͠ļ͚͉̗̘̽͑̿͑̚o̼̰̼͕̞̍̄̎̿̊,̻̰̻̘́̎͒̋͘͟ ̧̬̝͈̬̿͌̿̑̕ẉ̣̟͉̮͆̊̃͐̈́ờ̢̫͎͖̹͊́͐r̨̮͓͓̣̅̋͐͐͆ḻ̩̦͚̯͑̌̓̅͒d͇̯͔̼͍͛̾͛͡͝',
                Range\between(0, 11, true),
            ],
        ];
    }

    public function testRangeThrowsForOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Grapheme\range('Hello', Range\from(10));
    }

    public function testRangeThrowsForNegativeOutOfBoundOffset(): void
    {
        $this->expectException(Exception\OutOfBoundsException::class);

        Grapheme\range('Hello', Range\from(-6));
    }
}
