<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Decoder;
use Psl\HPACK\Encoder;
use Psl\HPACK\Exception\DecodingException;
use Psl\HPACK\Exception\HeaderListSizeException;
use Psl\HPACK\Exception\InvalidSizeException;
use Psl\HPACK\Header;

use function ord;
use function str_repeat;
use function strlen;

final class EncoderTest extends TestCase
{
    public function testEmptyHeaderList(): void
    {
        $encoder = new Encoder();

        static::assertSame('', $encoder->encode([]));
    }

    public function testSensitiveHeaderNeverIndexed(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([
            new Header('authorization', 'secret', true),
        ]);

        static::assertSame(0b0001_0000, ord($encoded[0]) & 0b1111_0000);
    }

    public function testIndexedHeaderField(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
        ]);

        static::assertSame("\x82", $encoded);
    }

    public function testMultipleIndexedHeaders(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'http'),
            new Header(':path', '/'),
        ]);

        static::assertSame("\x82\x86\x84", $encoded);
    }

    public function testTableSizeUpdateAfterResize(): void
    {
        $encoder = new Encoder();
        $encoder->resize(256);

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
        ]);

        static::assertSame(0b0010_0000, ord($encoded[0]) & 0b1110_0000);
    }

    public function testDynamicTableEvolution(): void
    {
        $encoder = new Encoder();

        $encoder->encode([
            new Header('custom-key', 'custom-value'),
        ]);

        $encoded = $encoder->encode([
            new Header('custom-key', 'custom-value'),
        ]);

        static::assertSame(0b1000_0000, ord($encoded[0]) & 0b1000_0000);
    }

    public function testRfcC4Request1(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'http'),
            new Header(':path', '/'),
            new Header(':authority', 'www.example.com'),
        ]);

        static::assertSame(hex2bin('828684418cf1e3c2e5f23a6ba0ab90f4ff'), $encoded);
    }

    public function testRfcC4Request2(): void
    {
        $encoder = new Encoder();

        $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'http'),
            new Header(':path', '/'),
            new Header(':authority', 'www.example.com'),
        ]);

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'http'),
            new Header(':path', '/'),
            new Header(':authority', 'www.example.com'),
            new Header('cache-control', 'no-cache'),
        ]);

        static::assertSame(hex2bin('828684be5886a8eb10649cbf'), $encoded);
    }

    public function testRfcC4Request3(): void
    {
        $encoder = new Encoder();

        $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'http'),
            new Header(':path', '/'),
            new Header(':authority', 'www.example.com'),
        ]);

        $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'http'),
            new Header(':path', '/'),
            new Header(':authority', 'www.example.com'),
            new Header('cache-control', 'no-cache'),
        ]);

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/index.html'),
            new Header(':authority', 'www.example.com'),
            new Header('custom-key', 'custom-value'),
        ]);

        static::assertSame(hex2bin('828785bf408825a849e95ba97d7f8925a849e95bb8e8b4bf'), $encoded);
    }

    public function testResizeEmitsCorrectTableSizeUpdate(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->encode([new Header('custom-key', 'custom-value')]);
        $decoder->decode($encoder->encode([new Header(':method', 'GET')]));

        $encoder->resize(128);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);

        $byte = ord($encoded[0]);
        static::assertSame(0b0010_0000, $byte & 0b1110_0000);

        $decoder->resize(128);
        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testResizeActuallyUpdatesTable(): void
    {
        $encoder = new Encoder();

        $encoder->encode([new Header('custom-key', 'custom-value')]);

        $first = $encoder->encode([new Header('custom-key', 'custom-value')]);
        static::assertSame(0b1000_0000, ord($first[0]) & 0b1000_0000);

        $encoder->resize(0);
        $encoder->resize(4096);

        $encoded = $encoder->encode([new Header('custom-key', 'custom-value')]);
        $hasIndexed = strlen($encoded) === 1 && (ord($encoded[0]) & 0b1000_0000) !== 0;
        static::assertFalse($hasIndexed);
    }

    public function testSensitiveWithStaticNameMatch(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([
            new Header(':method', 'PATCH', true),
        ]);

        static::assertSame(0b0001_0000, ord($encoded[0]) & 0b1111_0000);

        $decoder = new Decoder();
        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('PATCH', $decoded[0]->value);
        static::assertTrue($decoded[0]->sensitive);
    }

    public function testSensitiveWithDynamicNameMatch(): void
    {
        $encoder = new Encoder();

        $encoder->encode([new Header('x-custom', 'value1')]);

        $encoded = $encoder->encode([
            new Header('x-custom', 'secret-value', true),
        ]);

        static::assertSame(0b0001_0000, ord($encoded[0]) & 0b1111_0000);

        $index = ord($encoded[0]) & 0b0000_1111;
        static::assertGreaterThan(0, $index);
    }

    public function testSensitiveWithNoNameMatch(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([
            new Header('x-totally-new', 'secret', true),
        ]);

        static::assertSame("\x10", $encoded[0]);
    }

    public function testRawStringEncodingWhenHuffmanNotSmaller(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([new Header('x-bin', "\x00\x01\x02")]);

        $decoder = new Decoder();
        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame("\x00\x01\x02", $decoded[0]->value);
    }

    public function testDynamicNameMatchPreferredOverNone(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoded1 = $encoder->encode([new Header('x-unique-header', 'value1')]);
        $decoder->decode($encoded1);

        $encoded2 = $encoder->encode([new Header('x-unique-header', 'value2')]);

        static::assertSame(0b0100_0000, ord($encoded2[0]) & 0b1100_0000);

        $decoded = $decoder->decode($encoded2);
        static::assertCount(1, $decoded);
        static::assertSame('x-unique-header', $decoded[0]->name);
        static::assertSame('value2', $decoded[0]->value);
    }

    public function testSensitiveNoNameMatchRoundTrip(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoded = $encoder->encode([
            new Header('x-brand-new', 'secret-data', true),
        ]);

        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame('x-brand-new', $decoded[0]->name);
        static::assertSame('secret-data', $decoded[0]->value);
        static::assertTrue($decoded[0]->sensitive);
    }

    public function testSensitiveWithDynamicNameMatchRoundTrip(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoded1 = $encoder->encode([new Header('x-token', 'public')]);
        $decoder->decode($encoded1);

        $encoded2 = $encoder->encode([
            new Header('x-token', 'private-value', true),
        ]);

        $decoded = $decoder->decode($encoded2);
        static::assertCount(1, $decoded);
        static::assertSame('x-token', $decoded[0]->name);
        static::assertSame('private-value', $decoded[0]->value);
        static::assertTrue($decoded[0]->sensitive);
    }

    public function testResizeToLargeValueEmitsCorrectWireFormat(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(500);
        $decoder->resize(500);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);
        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testEncoderResizeEvictsDynamicEntries(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $e1 = $encoder->encode([new Header('x-key', 'x-value')]);
        $decoder->decode($e1);

        $e2 = $encoder->encode([new Header('x-key', 'x-value')]);
        static::assertSame(0b1000_0000, ord($e2[0]) & 0b1000_0000);
        $decoder->decode($e2);

        $encoder->resize(0);
        $decoder->resize(0);

        $e3 = $encoder->encode([new Header('x-key', 'x-value')]);
        static::assertNotSame(0b1000_0000, ord($e3[0]) & 0b1000_0000);
    }

    public function testDynamicNameMatchNotOverriddenByStaticNameMatch(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $e1 = $encoder->encode([new Header('content-type', 'text/html')]);
        $decoder->decode($e1);

        $e2 = $encoder->encode([new Header('content-type', 'application/json')]);
        $decoded = $decoder->decode($e2);

        static::assertCount(1, $decoded);
        static::assertSame('content-type', $decoded[0]->name);
        static::assertSame('application/json', $decoded[0]->value);
    }

    public function testResizeToZeroRoundTrip(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $e1 = $encoder->encode([new Header('x-key', 'x-value')]);
        $decoder->decode($e1);

        $encoder->resize(0);

        $e2 = $encoder->encode([new Header('x-key', 'x-value')]);
        $decoded = $decoder->decode($e2);
        static::assertCount(1, $decoded);
        static::assertSame('x-key', $decoded[0]->name);
        static::assertSame('x-value', $decoded[0]->value);
    }

    public function testSensitiveWithDynamicNameMatchNotFirstEntry(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $e1 = $encoder->encode([
            new Header('x-first', 'value1'),
            new Header('x-second', 'value2'),
        ]);
        $decoder->decode($e1);

        $e2 = $encoder->encode([
            new Header('x-first', 'secret1', true),
        ]);
        $decoded = $decoder->decode($e2);
        static::assertCount(1, $decoded);
        static::assertSame('x-first', $decoded[0]->name);
        static::assertSame('secret1', $decoded[0]->value);
        static::assertTrue($decoded[0]->sensitive);
    }

    public function testSensitiveWithStaticNameMatchVerifyIndex(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoded = $encoder->encode([
            new Header(':status', '999', true),
        ]);

        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame(':status', $decoded[0]->name);
        static::assertSame('999', $decoded[0]->value);
        static::assertTrue($decoded[0]->sensitive);

        static::assertSame(0b0001_0000, ord($encoded[0]) & 0b1111_0000);
        $index = ord($encoded[0]) & 0b0000_1111;
        static::assertSame(8, $index);
    }

    public function testResizeEmitsExactPrefixByte(): void
    {
        $encoder = new Encoder();
        $encoder->resize(16);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);

        static::assertSame("\x30", $encoded[0]);
    }

    public function testConstructorWithZeroTableSize(): void
    {
        $encoder = new Encoder(0);

        $encoder->encode([new Header('x-key', 'x-value')]);

        $second = $encoder->encode([new Header('x-key', 'x-value')]);
        static::assertNotSame(0b1000_0000, ord($second[0]) & 0b1000_0000);
    }

    public function testConstructorWithZeroHeaderListSize(): void
    {
        $encoder = new Encoder(4096, 0);

        $encoder->encode([]);

        $this->expectException(HeaderListSizeException::class);
        $encoder->encode([new Header(':method', 'GET')]);
    }

    public function testConstructorRejectsNegativeTableSize(): void
    {
        $this->expectException(InvalidSizeException::class);
        new Encoder(-1);
    }

    public function testConstructorRejectsNegativeHeaderListSize(): void
    {
        $this->expectException(InvalidSizeException::class);
        new Encoder(4096, -1);
    }

    public function testDefaultMaxTableSizeIs4096(): void
    {
        $encoder = new Encoder();

        $value = str_repeat('a', 4_063);
        $encoder->encode([new Header('x', $value)]);

        $second = $encoder->encode([new Header('x', $value)]);
        static::assertSame(0b1000_0000, ord($second[0]) & 0b1000_0000);
    }

    public function testDefaultMaxTableSizeExcludes4095(): void
    {
        $encoder = new Encoder();

        $value = str_repeat('a', 4_063);
        $encoder->encode([new Header('x', $value)]);
        $second = $encoder->encode([new Header('x', $value)]);
        static::assertSame(0b1000_0000, ord($second[0]) & 0b1000_0000);

        $encoder2 = new Encoder();
        $value2 = str_repeat('a', 4_064);
        $encoder2->encode([new Header('x', $value2)]);
        $third = $encoder2->encode([new Header('x', $value2)]);
        static::assertNotSame(0b1000_0000, ord($third[0]) & 0b1000_0000);
    }

    public function testDefaultMaxHeaderListSizeAtExactBoundary(): void
    {
        $encoder = new Encoder();
        $result = $encoder->encode([new Header('x', str_repeat('a', 16_351))]);

        static::assertNotSame('', $result);
    }

    public function testDefaultMaxHeaderListSizeOneOverThrows(): void
    {
        $encoder = new Encoder();

        $this->expectException(HeaderListSizeException::class);
        $encoder->encode([new Header('x', str_repeat('a', 16_352))]);
    }

    public function testHeaderListSizeExactBoundaryDoesNotThrow(): void
    {
        $encoder = new Encoder(4096, 100);
        $result = $encoder->encode([new Header('x', str_repeat('a', 67))]);

        static::assertNotSame('', $result);
    }

    public function testHeaderListSizeOneOverBoundaryThrows(): void
    {
        $encoder = new Encoder(4096, 100);

        $this->expectException(HeaderListSizeException::class);
        $encoder->encode([new Header('x', str_repeat('a', 68))]);
    }

    public function testResizeEmitsMinThenFinalTableSizeUpdates(): void
    {
        $encoder = new Encoder();

        $encoder->resize(32);
        $encoder->resize(100);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);

        static::assertSame("\x3F\x01\x3F\x45\x82", $encoded);
    }

    public function testResizeEmitsMinThenFinalRoundTrip(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(32);
        $encoder->resize(100);
        $decoder->resize(100);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testResizeTracksMinimuAcrossMultipleCalls(): void
    {
        $encoder = new Encoder();

        $encoder->resize(200);
        $encoder->resize(50);
        $encoder->resize(150);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);

        static::assertSame("\x3F\x13\x3F\x77\x82", $encoded);
    }

    public function testResizeNeverUpdatesMinToLargerValue(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $e1 = $encoder->encode([new Header('x-key', 'x-value')]);
        $decoder->decode($e1);

        $encoder->resize(10);
        $encoder->resize(200);
        $decoder->resize(200);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);

        static::assertSame(0b0010_0000, ord($encoded[0]) & 0b1110_0000);
    }

    public function testStaticNameMatchNotOverriddenByDynamic(): void
    {
        $encoder = new Encoder();

        $encoder->encode([new Header('content-type', 'text/html')]);

        $e2 = $encoder->encode([new Header('content-type', 'application/json')]);

        static::assertSame(0x5F, ord($e2[0]));
    }

    public function testRawEncodingUsedWhenHuffmanEqualLength(): void
    {
        $encoder = new Encoder();

        $encoded = $encoder->encode([new Header(':method', 'A')]);

        static::assertSame(0, ord($encoded[1]) & 0x80);

        $decoder = new Decoder();
        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame('A', $decoded[0]->value);
    }

    public function testResizeRejectsNegativeSize(): void
    {
        $encoder = new Encoder();

        $this->expectException(InvalidSizeException::class);
        $encoder->resize(-1);
    }

    public function testSingleResizeEmitsOneTableSizeUpdate(): void
    {
        $encoder = new Encoder();
        $encoder->resize(16);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);

        static::assertSame("\x30\x82", $encoded);
    }

    public function testSetMaxHeaderListSizeAcceptsZero(): void
    {
        $encoder = new Encoder();
        $encoder->setMaxHeaderListSize(0);

        $this->expectException(HeaderListSizeException::class);
        $encoder->encode([new Header(':method', 'GET')]);
    }

    public function testSetMaxHeaderListSizeRejectsNegative(): void
    {
        $encoder = new Encoder();

        $this->expectException(InvalidSizeException::class);
        $encoder->setMaxHeaderListSize(-1);
    }

    public function testResizeMinUpdateWithSmallValue(): void
    {
        $encoder = new Encoder();

        $encoder->resize(10);
        $encoder->resize(50);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);

        static::assertSame("\x2A\x3F\x13\x82", $encoded);
    }

    public function testEncodeResponseHeaders(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoded = $encoder->encodeWithStatus('200', [
            new Header('content-type', 'text/html'),
            new Header('x-custom', 'hello'),
        ]);

        $decoded = $decoder->decode($encoded);

        static::assertCount(3, $decoded);
        static::assertSame(':status', $decoded[0]->name);
        static::assertSame('200', $decoded[0]->value);
        static::assertSame('content-type', $decoded[1]->name);
        static::assertSame('text/html', $decoded[1]->value);
        static::assertSame('x-custom', $decoded[2]->name);
        static::assertSame('hello', $decoded[2]->value);
    }

    public function testEncodeResponseHeadersExceedingListSize(): void
    {
        $encoder = new Encoder(4096, 100);

        $this->expectException(HeaderListSizeException::class);

        $encoder->encodeWithStatus('200', [
            new Header('x-large', str_repeat('a', 100)),
        ]);
    }

    public function testEncodeResponseHeadersStatusAloneExceedingListSize(): void
    {
        $encoder = new Encoder(4096, 30);

        $this->expectException(HeaderListSizeException::class);

        $encoder->encodeWithStatus('200', []);
    }

    public function testResizeTriggersTableSizeUpdateOnNextEncode(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(256);
        $decoder->resize(256);

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
        ]);

        static::assertSame(0b0010_0000, ord($encoded[0]) & 0b1110_0000);

        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testDoubleResizeTriggersMinAndFinalUpdates(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(64);
        $encoder->resize(512);
        $decoder->resize(512);

        $encoded = $encoder->encode([
            new Header(':method', 'GET'),
        ]);

        static::assertSame(0b0010_0000, ord($encoded[0]) & 0b1110_0000);

        $secondUpdateOffset = $encoded[0] === "\x3F" ? 2 : 1;
        static::assertSame(0b0010_0000, ord($encoded[$secondUpdateOffset]) & 0b1110_0000);

        $decoded = $decoder->decode($encoded);
        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testTableSizeUpdateAfterHeaderEntryThrows(): void
    {
        $tableSizeUpdate = "\x3F\x01";
        $getIndexed = "\x82";

        $block = $getIndexed . $tableSizeUpdate;

        $decoder = new Decoder();

        $this->expectException(DecodingException::class);
        $this->expectExceptionMessage('Dynamic table size update must occur at the start of a header block.');

        $decoder->decode($block);
    }

    public function testEncodeResponseHeadersWithSensitiveHeader(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoded = $encoder->encodeWithStatus('404', [
            new Header('x-secret', 'classified', true),
        ]);

        $decoded = $decoder->decode($encoded);

        static::assertCount(2, $decoded);
        static::assertSame(':status', $decoded[0]->name);
        static::assertSame('404', $decoded[0]->value);
        static::assertSame('x-secret', $decoded[1]->name);
        static::assertSame('classified', $decoded[1]->value);
        static::assertTrue($decoded[1]->sensitive);
    }

    public function testEncodeResponseHeadersWithPendingTableSizeUpdate(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(256);
        $decoder->resize(256);

        $encoded = $encoder->encodeWithStatus('200', [
            new Header('content-type', 'text/plain'),
        ]);

        static::assertSame(0b0010_0000, ord($encoded[0]) & 0b1110_0000);

        $decoded = $decoder->decode($encoded);
        static::assertCount(2, $decoded);
        static::assertSame(':status', $decoded[0]->name);
        static::assertSame('200', $decoded[0]->value);
    }
}
