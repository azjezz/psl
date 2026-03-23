<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Edns;

use PHPUnit\Framework\TestCase;
use Psl\DNS\EDNS\RawOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;

final class RawOptionTest extends TestCase
{
    public function testProperties(): void
    {
        $option = new RawOption(99, "\xAB\xCD");

        static::assertSame(99, $option->code);
        static::assertSame("\xAB\xCD", $option->data);
    }

    public function testToWireFormatReturnsDataUnchanged(): void
    {
        $data = "\x01\x02\x03\x04";
        $option = new RawOption(15, $data);

        static::assertSame($data, $option->toWireFormat());
    }

    public function testToWireFormatEmptyData(): void
    {
        $option = new RawOption(99, '');

        static::assertSame('', $option->toWireFormat());
    }

    public function testRoundTrip(): void
    {
        $original = new RawOption(99, "\xAB\xCD\xEF");
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $raw = $decoded[0];
        static::assertInstanceOf(RawOption::class, $raw);
        static::assertSame(99, $raw->code);
        static::assertSame("\xAB\xCD\xEF", $raw->data);
    }
}
