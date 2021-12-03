<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Html;

use PHPUnit\Framework\TestCase;
use Psl\Html;
use Psl\Str;

final class EncodeSpecialCharactersTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEncode(string $expected, string $html, bool $double_encoding, Str\Encoding $encoding): void
    {
        static::assertSame($expected, Html\encode_special_characters($html, $double_encoding, $encoding));
    }

    public function provideData(): iterable
    {
        yield ['hello', 'hello', true, Str\Encoding::UTF_8];
        yield ['héllo', 'héllo', true, Str\Encoding::UTF_8];
        yield ['&lt;hello /&gt;', '<hello />', true, Str\Encoding::UTF_8];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', true, Str\Encoding::UTF_8];
        yield ['&lt;p&gt;&amp;lt; &lt;/p&gt;', '<p>&lt; </p>', true, Str\Encoding::UTF_8];

        yield ['hello', 'hello', false, Str\Encoding::UTF_8];
        yield ['héllo', 'héllo', false, Str\Encoding::UTF_8];
        yield ['&lt;hello /&gt;', '<hello />', false, Str\Encoding::UTF_8];
        yield ['&lt;p&gt;Hello&lt;/p&gt;', '<p>Hello</p>', false, Str\Encoding::UTF_8];
        yield ['&lt;p&gt;&lt; &lt;/p&gt;', '<p>&lt; </p>', false, Str\Encoding::UTF_8];
    }
}
