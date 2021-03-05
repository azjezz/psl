<?php

declare(strict_types=1);

namespace Psl\Tests\Html;

use PHPUnit\Framework\TestCase;
use Psl\Html;

final class DecodeSpecialCharactersTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEncode(string $expected, string $html): void
    {
        static::assertSame($expected, Html\decode_special_characters($html));
    }

    public function provideData(): iterable
    {
        yield ['hello', 'hello'];
        yield ['héllo', 'héllo'];
        yield ['<hello />', '&lt;hello /&gt;'];
        yield ['<p>Hello</p>', '&lt;p&gt;Hello&lt;/p&gt;'];
        yield ['<p>&lt; </p>', '&lt;p&gt;&amp;lt; &lt;/p&gt;'];

        yield ['hello', 'hello'];
        yield ['héllo', 'héllo'];
        yield ['<hello />', '&lt;hello /&gt;'];
        yield ['<p>Hello</p>', '&lt;p&gt;Hello&lt;/p&gt;'];
        yield ['<p>< </p>', '&lt;p&gt;&lt; &lt;/p&gt;'];
    }
}
