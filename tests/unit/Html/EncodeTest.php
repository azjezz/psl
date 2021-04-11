<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Html;

use PHPUnit\Framework\TestCase;
use Psl\Html;

final class EncodeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEncode(string $expected, string $html, bool $double_encoding, ?string $encoding): void
    {
        static::assertSame($expected, Html\encode($html, $double_encoding, $encoding));
    }

    public function provideData(): iterable
    {
        yield ['hello', 'hello', true, 'UTF-8'];
        yield ['h&eacute;llo', 'héllo', true, 'UTF-8'];
        yield ['&lt;hello /&gt;', '<hello />', true, 'UTF-8'];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', true, 'UTF-8'];
        yield ['&lt;p&gt;&amp;lt; &lt;/p&gt;', '<p>&lt; </p>', true, 'UTF-8'];

        yield ['hello', 'hello', false, 'UTF-8'];
        yield ['h&eacute;llo', 'héllo', false, 'UTF-8'];
        yield ['&lt;hello /&gt;', '<hello />', false, 'UTF-8'];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', false, 'UTF-8'];
        yield ['&lt;p&gt;&lt; &lt;/p&gt;', '<p>&lt; </p>', false, 'UTF-8'];
    }
}
