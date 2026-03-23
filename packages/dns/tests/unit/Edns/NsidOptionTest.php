<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Edns;

use PHPUnit\Framework\TestCase;
use Psl\DNS\EDNS\NSIDOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;

final class NsidOptionTest extends TestCase
{
    public function testCode(): void
    {
        $option = new NSIDOption();

        static::assertSame(3, $option->code);
    }

    public function testEmptyId(): void
    {
        $option = new NSIDOption();

        static::assertSame('', $option->id);
    }

    public function testWithId(): void
    {
        $option = new NSIDOption('ns1.example.com');

        static::assertSame('ns1.example.com', $option->id);
    }

    public function testToWireFormatEmpty(): void
    {
        $option = new NSIDOption();

        static::assertSame('', $option->toWireFormat());
    }

    public function testToWireFormatWithId(): void
    {
        $option = new NSIDOption("\x01\x02\x03");

        static::assertSame("\x01\x02\x03", $option->toWireFormat());
    }

    public function testRoundTripEmpty(): void
    {
        $original = new NSIDOption();
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $nsid = $decoded[0];
        static::assertInstanceOf(NSIDOption::class, $nsid);
        static::assertSame('', $nsid->id);
    }

    public function testRoundTripWithId(): void
    {
        $original = new NSIDOption('ns1.example.com');
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $nsid = $decoded[0];
        static::assertInstanceOf(NSIDOption::class, $nsid);
        static::assertSame('ns1.example.com', $nsid->id);
    }
}
