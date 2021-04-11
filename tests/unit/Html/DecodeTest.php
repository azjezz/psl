<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Html;

use PHPUnit\Framework\TestCase;
use Psl\Html;

final class DecodeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEncode(string $expected, string $html, ?string $encoding): void
    {
        static::assertSame($expected, Html\decode($html, $encoding));
    }

    public function provideData(): iterable
    {
        yield ['hello', 'hello', 'UTF-8'];
        yield ['héllo', 'h&eacute;llo', 'UTF-8'];
        yield ['<hello />', '&lt;hello /&gt;', 'UTF-8'];
        yield ['<p>Hello</p>', '&lt;p&gt;Hello&lt;/p&gt;', 'UTF-8'];
        yield ['<p>&lt; </p>', '&lt;p&gt;&amp;lt; &lt;/p&gt;', 'UTF-8'];

        yield ['hello', 'hello', 'UTF-8'];
        yield ['héllo', 'h&eacute;llo', 'UTF-8'];
        yield ['<hello />', '&lt;hello /&gt;', 'UTF-8'];
        yield ['<p>Hello</p>', '&lt;p&gt;Hello&lt;/p&gt;', 'UTF-8'];
        yield ['<p>< </p>', '&lt;p&gt;&lt; &lt;/p&gt;', 'UTF-8'];
    }
}
