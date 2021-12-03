<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Html;

use PHPUnit\Framework\TestCase;
use Psl\Html;
use Psl\Str;

final class DecodeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEncode(string $expected, string $html, Str\Encoding $encoding): void
    {
        static::assertSame($expected, Html\decode($html, $encoding));
    }

    public function provideData(): iterable
    {
        yield ['hello', 'hello', Str\Encoding::UTF_8];
        yield ['héllo', 'h&eacute;llo', Str\Encoding::UTF_8];
        yield ['<hello />', '&lt;hello /&gt;', Str\Encoding::UTF_8];
        yield ['<p>Hello</p>', '&lt;p&gt;Hello&lt;/p&gt;', Str\Encoding::UTF_8];
        yield ['<p>&lt; </p>', '&lt;p&gt;&amp;lt; &lt;/p&gt;', Str\Encoding::UTF_8];

        yield ['hello', 'hello', Str\Encoding::UTF_8];
        yield ['héllo', 'h&eacute;llo', Str\Encoding::UTF_8];
        yield ['<hello />', '&lt;hello /&gt;', Str\Encoding::UTF_8];
        yield ['<p>Hello</p>', '&lt;p&gt;Hello&lt;/p&gt;', Str\Encoding::UTF_8];
        yield ['<p>< </p>', '&lt;p&gt;&lt; &lt;/p&gt;', Str\Encoding::UTF_8];
    }
}
