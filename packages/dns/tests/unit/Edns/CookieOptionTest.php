<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Edns;

use PHPUnit\Framework\TestCase;
use Psl\DNS\EDNS\CookieOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;
use Psl\Str\Byte;

final class CookieOptionTest extends TestCase
{
    public function testCode(): void
    {
        $option = new CookieOption("\x01\x02\x03\x04\x05\x06\x07\x08");

        static::assertSame(10, $option->code);
    }

    public function testPropertiesClientOnly(): void
    {
        $option = new CookieOption("\x01\x02\x03\x04\x05\x06\x07\x08");

        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $option->clientCookie);
        static::assertSame('', $option->serverCookie);
    }

    public function testPropertiesWithServerCookie(): void
    {
        $option = new CookieOption("\x01\x02\x03\x04\x05\x06\x07\x08", "\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8");

        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $option->clientCookie);
        static::assertSame("\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8", $option->serverCookie);
    }

    public function testToWireFormatClientOnly(): void
    {
        $option = new CookieOption("\x01\x02\x03\x04\x05\x06\x07\x08");

        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $option->toWireFormat());
    }

    public function testToWireFormatWithServerCookie(): void
    {
        $clientCookie = "\x01\x02\x03\x04\x05\x06\x07\x08";
        $serverCookie = "\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8";
        $option = new CookieOption($clientCookie, $serverCookie);

        static::assertSame($clientCookie . $serverCookie, $option->toWireFormat());
    }

    public function testRoundTripClientOnly(): void
    {
        $original = new CookieOption("\x01\x02\x03\x04\x05\x06\x07\x08");
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $cookie = $decoded[0];
        static::assertInstanceOf(CookieOption::class, $cookie);
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $cookie->clientCookie);
        static::assertSame('', $cookie->serverCookie);
    }

    public function testRoundTripWithServerCookie(): void
    {
        $original = new CookieOption("\x01\x02\x03\x04\x05\x06\x07\x08", "\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8");
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $cookie = $decoded[0];
        static::assertInstanceOf(CookieOption::class, $cookie);
        static::assertSame("\x01\x02\x03\x04\x05\x06\x07\x08", $cookie->clientCookie);
        static::assertSame("\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8", $cookie->serverCookie);
    }

    public function testRoundTripWithLargeServerCookie(): void
    {
        $serverCookie =
            "\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10"
            . "\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x20";
        $original = new CookieOption("\xAA\xBB\xCC\xDD\xEE\xFF\x00\x11", $serverCookie);
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $cookie = $decoded[0];
        static::assertInstanceOf(CookieOption::class, $cookie);
        static::assertSame("\xAA\xBB\xCC\xDD\xEE\xFF\x00\x11", $cookie->clientCookie);
        static::assertSame($serverCookie, $cookie->serverCookie);
    }

    public function testCreateRandom(): void
    {
        $option = CookieOption::random();

        static::assertSame(8, Byte\length($option->clientCookie));
        static::assertSame('', $option->serverCookie);
    }

    public function testCreateRandomProducesDifferentCookies(): void
    {
        $a = CookieOption::random();
        $b = CookieOption::random();

        static::assertNotSame($a->clientCookie, $b->clientCookie);
    }
}
