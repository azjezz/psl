<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Html;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Html;

final class EncodeTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testEncode(string $expected, string $html, bool $doubleEncoding, Html\Encoding $encoding): void
    {
        static::assertSame($expected, Html\encode($html, $doubleEncoding, $encoding));
    }

    public static function provideData(): iterable
    {
        yield ['hello', 'hello', true, Html\Encoding::Utf8];
        yield ['h&eacute;llo', 'héllo', true, Html\Encoding::Utf8];
        yield ['&lt;hello /&gt;', '<hello />', true, Html\Encoding::Utf8];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', true, Html\Encoding::Utf8];
        yield ['&lt;p&gt;&amp;lt; &lt;/p&gt;', '<p>&lt; </p>', true, Html\Encoding::Utf8];

        yield ['hello', 'hello', false, Html\Encoding::Utf8];
        yield ['h&eacute;llo', 'héllo', false, Html\Encoding::Utf8];
        yield ['&lt;hello /&gt;', '<hello />', false, Html\Encoding::Utf8];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', false, Html\Encoding::Utf8];
        yield ['&lt;p&gt;&lt; &lt;/p&gt;', '<p>&lt; </p>', false, Html\Encoding::Utf8];
    }
}
