<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Edns;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\EDNS\ExtendedDNSError;
use Psl\DNS\EDNS\ExtendedDNSErrorOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;

final class ExtendedDnsErrorOptionTest extends TestCase
{
    public function testCode(): void
    {
        $option = new ExtendedDNSErrorOption(0);

        static::assertSame(15, $option->code);
    }

    public function testInfoCodeOnly(): void
    {
        $option = new ExtendedDNSErrorOption(6);

        static::assertSame(6, $option->infoCode);
        static::assertSame('', $option->extraText);
    }

    public function testWithExtraText(): void
    {
        $option = new ExtendedDNSErrorOption(6, 'DNSSEC validation failed');

        static::assertSame(6, $option->infoCode);
        static::assertSame('DNSSEC validation failed', $option->extraText);
    }

    public function testToWireFormatCodeOnly(): void
    {
        $option = new ExtendedDNSErrorOption(6);

        $expected = new Writer()->u16(6)->toString();

        static::assertSame($expected, $option->toWireFormat());
    }

    public function testToWireFormatWithText(): void
    {
        $option = new ExtendedDNSErrorOption(6, 'bogus');

        $expected = new Writer()
            ->u16(6)
            ->bytes('bogus')
            ->toString();

        static::assertSame($expected, $option->toWireFormat());
    }

    public function testRoundTripCodeOnly(): void
    {
        $original = new ExtendedDNSErrorOption(15);
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $ede = $decoded[0];
        static::assertInstanceOf(ExtendedDNSErrorOption::class, $ede);
        static::assertSame(15, $ede->infoCode);
        static::assertSame('', $ede->extraText);
    }

    public function testRoundTripWithText(): void
    {
        $original = new ExtendedDNSErrorOption(6, 'DNSSEC validation failed');
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $ede = $decoded[0];
        static::assertInstanceOf(ExtendedDNSErrorOption::class, $ede);
        static::assertSame(6, $ede->infoCode);
        static::assertSame('DNSSEC validation failed', $ede->extraText);
    }

    public function testExtendedDnsErrorEnumKnownCode(): void
    {
        $error = ExtendedDNSError::tryFrom(6);

        static::assertSame(ExtendedDNSError::DnssecBogus, $error);
    }

    public function testExtendedDnsErrorEnumUnknownCode(): void
    {
        $error = ExtendedDNSError::tryFrom(9999);

        static::assertNull($error);
    }
}
