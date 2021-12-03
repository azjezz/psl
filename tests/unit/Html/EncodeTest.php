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
    public function testEncode(string $expected, string $html, bool $double_encoding, Html\Encoding $encoding): void
    {
        static::assertSame($expected, Html\encode($html, $double_encoding, $encoding));
    }

    public function provideData(): iterable
    {
        yield ['hello', 'hello', true, Html\Encoding::UTF_8];
        yield ['h&eacute;llo', 'héllo', true, Html\Encoding::UTF_8];
        yield ['&lt;hello /&gt;', '<hello />', true, Html\Encoding::UTF_8];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', true, Html\Encoding::UTF_8];
        yield ['&lt;p&gt;&amp;lt; &lt;/p&gt;', '<p>&lt; </p>', true, Html\Encoding::UTF_8];

        yield ['hello', 'hello', false, Html\Encoding::UTF_8];
        yield ['h&eacute;llo', 'héllo', false, Html\Encoding::UTF_8];
        yield ['&lt;hello /&gt;', '<hello />', false, Html\Encoding::UTF_8];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', false, Html\Encoding::UTF_8];
        yield ['&lt;p&gt;&lt; &lt;/p&gt;', '<p>&lt; </p>', false, Html\Encoding::UTF_8];
    }
}
