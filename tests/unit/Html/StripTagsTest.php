<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Html;

use PHPUnit\Framework\TestCase;
use Psl\Html;

final class StripTagsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEncode(string $expected, string $html, array $allowed_tags): void
    {
        static::assertSame($expected, Html\strip_tags($html, $allowed_tags));
    }

    public function provideData(): iterable
    {
        yield ['hello', 'hello', []];
        yield ['hello', '<p>hello</p>', []];
        yield ['<p>hello</p>', '<p>hello</p>', ['p']];
        yield ['<p>hello</p>', '<p>hello</p>', ['p', 'span']];
        yield ['hello, <span>world!</span>', '<p>hello, <span>world!</span></p>', ['span']];
        yield ['<p>hello, world!</p>', '<p>hello, <span>world!</span></p>', ['p']];
        yield ['hello, world!', '<p>hello, <span>world!</span></p>', []];
    }
}
