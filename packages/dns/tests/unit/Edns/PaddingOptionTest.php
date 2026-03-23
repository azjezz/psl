<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Edns;

use PHPUnit\Framework\TestCase;
use Psl\DNS\EDNS\PaddingOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;
use Psl\Str\Byte;

final class PaddingOptionTest extends TestCase
{
    public function testCode(): void
    {
        $option = new PaddingOption(0);

        static::assertSame(12, $option->code);
    }

    public function testZeroLength(): void
    {
        $option = new PaddingOption(0);

        static::assertSame(0, $option->length);
    }

    public function testNonZeroLength(): void
    {
        $option = new PaddingOption(128);

        static::assertSame(128, $option->length);
    }

    public function testToWireFormatZero(): void
    {
        $option = new PaddingOption(0);

        static::assertSame('', $option->toWireFormat());
    }

    public function testToWireFormatNonZero(): void
    {
        $option = new PaddingOption(4);

        static::assertSame("\x00\x00\x00\x00", $option->toWireFormat());
        static::assertSame(4, Byte\length($option->toWireFormat()));
    }

    public function testRoundTripZero(): void
    {
        $original = new PaddingOption(0);
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $padding = $decoded[0];
        static::assertInstanceOf(PaddingOption::class, $padding);
        static::assertSame(0, $padding->length);
    }

    public function testRoundTripNonZero(): void
    {
        $original = new PaddingOption(16);
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $padding = $decoded[0];
        static::assertInstanceOf(PaddingOption::class, $padding);
        static::assertSame(16, $padding->length);
    }
}
