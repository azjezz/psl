<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Frame;

use PHPUnit\Framework\TestCase;
use Psl\H2\ErrorCode;
use Psl\H2\Exception\FrameDecodingException;
use Psl\H2\Frame\AltSvcFrame;
use Psl\H2\Frame\DataFrame;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\GoAwayFrame;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Frame\OriginFrame;
use Psl\H2\Frame\PingFrame;
use Psl\H2\Frame\PushPromiseFrame;
use Psl\H2\Frame\RawFrame;
use Psl\H2\Frame\RstStreamFrame;
use Psl\H2\Frame\SettingsFrame;
use Psl\H2\Frame\WindowUpdateFrame;

use function chr;
use function ord;
use function pack;
use function str_repeat;
use function strlen;
use function substr;
use function unpack;

final class MutationCoverageTest extends TestCase
{
    // =========================================================================
    // AltSvcFrame (mutations 28-35)
    // =========================================================================

    public function testAltSvcFrameRoundTripExactBytes(): void
    {
        $frame = new AltSvcFrame(0, 'https://example.com', 'h3=":443"');
        $raw = $frame->toRaw();

        // Type must be AltSvc (0x0a)
        static::assertSame(FrameType::AltSvc->value, $raw->type);
        // Flags byte must be exactly 0
        static::assertSame(0, $raw->flags);
        // StreamId must be 0
        static::assertSame(0, $raw->streamId);

        // Payload: 2-byte origin length (big-endian) + origin + fieldValue
        $originLength = unpack('n', $raw->payload, 0)[1];
        static::assertSame(strlen('https://example.com'), $originLength);
        static::assertSame('https://example.com', substr($raw->payload, 2, $originLength));
        static::assertSame('h3=":443"', substr($raw->payload, 2 + $originLength));

        // Roundtrip
        $parsed = AltSvcFrame::fromRaw($raw);
        static::assertSame(0, $parsed->streamId);
        static::assertSame('https://example.com', $parsed->origin);
        static::assertSame('h3=":443"', $parsed->fieldValue);
    }

    public function testAltSvcFramePayloadMinimum2BytesCheck(): void
    {
        // A payload of exactly 1 byte should fail (< 2 check)
        $raw = new RawFrame(FrameType::AltSvc->value, 0, 0, "\x00");
        $this->expectException(FrameDecodingException::class);
        AltSvcFrame::fromRaw($raw);
    }

    public function testAltSvcFramePayloadExactly2BytesSucceeds(): void
    {
        // Payload of exactly 2 bytes with originLength=0 should succeed (boundary test for < vs <=)
        $raw = new RawFrame(FrameType::AltSvc->value, 0, 0, pack('n', 0));
        $parsed = AltSvcFrame::fromRaw($raw);
        static::assertSame('', $parsed->origin);
        static::assertSame('', $parsed->fieldValue);
    }

    public function testAltSvcFrameEmptyPayloadFails(): void
    {
        $raw = new RawFrame(FrameType::AltSvc->value, 0, 0, '');
        $this->expectException(FrameDecodingException::class);
        AltSvcFrame::fromRaw($raw);
    }

    public function testAltSvcFrameOriginLengthValidation(): void
    {
        // Origin length says 10, but payload only has 2 bytes for length + 3 bytes of data
        $raw = new RawFrame(FrameType::AltSvc->value, 0, 0, pack('n', 10) . 'abc');
        $this->expectException(FrameDecodingException::class);
        AltSvcFrame::fromRaw($raw);
    }

    public function testAltSvcFrameOriginLengthExactBoundary(): void
    {
        // Origin length exactly matches available bytes (2 + originLength == payloadLength)
        $origin = 'example.com';
        $raw = new RawFrame(FrameType::AltSvc->value, 0, 0, pack('n', strlen($origin)) . $origin);
        $parsed = AltSvcFrame::fromRaw($raw);
        static::assertSame($origin, $parsed->origin);
        static::assertSame('', $parsed->fieldValue);
    }

    public function testAltSvcFrameWithEmptyOrigin(): void
    {
        // originLength == 0 should produce empty origin string (tests originLength > 0 check)
        $frame = new AltSvcFrame(1, '', 'h2=":8000"');
        $raw = $frame->toRaw();

        $originLength = unpack('n', $raw->payload, 0)[1];
        static::assertSame(0, $originLength);

        $parsed = AltSvcFrame::fromRaw($raw);
        static::assertSame('', $parsed->origin);
        static::assertSame('h2=":8000"', $parsed->fieldValue);
        static::assertSame(1, $parsed->streamId);
    }

    public function testAltSvcFrameFlagsByteIsZero(): void
    {
        // Verify flags is exactly 0, not just falsy
        $frame = new AltSvcFrame(0, 'origin', 'value');
        $raw = $frame->toRaw();
        static::assertSame(0, $raw->flags);
        // Confirm 0 and not some other value that is falsy
        static::assertTrue(0 === $raw->flags);
    }

    public function testAltSvcFrameOnNonZeroStream(): void
    {
        $frame = new AltSvcFrame(5, '', 'h3=":443"');
        $raw = $frame->toRaw();
        static::assertSame(5, $raw->streamId);

        $parsed = AltSvcFrame::fromRaw($raw);
        static::assertSame(5, $parsed->streamId);
        static::assertSame('', $parsed->origin);
    }

    // =========================================================================
    // DataFrame (mutations 36-39)
    // =========================================================================

    public function testDataFramePaddedFlagParsingMinimum(): void
    {
        // Padded frame with empty payload (< 1 check) should fail
        $raw = new RawFrame(FrameType::Data->value, 0x08, 1, '');
        $this->expectException(FrameDecodingException::class);
        DataFrame::fromRaw($raw);
    }

    public function testDataFramePaddedWithExactly1Byte(): void
    {
        // Padded frame with exactly 1 byte (padLength=0, no data) should succeed
        $raw = new RawFrame(FrameType::Data->value, 0x08, 1, "\x00");
        $parsed = DataFrame::fromRaw($raw);
        static::assertSame('', $parsed->data);
    }

    public function testDataFramePadLengthEqualsPayloadLength(): void
    {
        // padLength >= payloadLength should throw (boundary: padLength == payloadLength - 1 would be no data)
        // padLength = 5, payload = chr(5) = 1 byte total, so padLength(5) >= payloadLength(1) => throw
        $raw = new RawFrame(FrameType::Data->value, 0x08, 1, chr(5));
        $this->expectException(FrameDecodingException::class);
        DataFrame::fromRaw($raw);
    }

    public function testDataFramePadLengthExactlyPayloadLengthMinus1(): void
    {
        // padLength = payloadLength - 1, means 0 bytes of data and 0 bytes of padding remaining
        // payload: chr(0) = padLength=0, payloadLength=1, padLength(0) < payloadLength(1) => OK
        $raw = new RawFrame(FrameType::Data->value, 0x08, 1, chr(0));
        $parsed = DataFrame::fromRaw($raw);
        static::assertSame('', $parsed->data);
    }

    public function testDataFrameDataLengthNonNegative(): void
    {
        // padLength that would make dataLength < 0
        // payload: chr(3) + "ab" = padLength=3, payloadLength=3
        // padLength(3) >= payloadLength(3) => throws in first check
        $raw = new RawFrame(FrameType::Data->value, 0x08, 1, chr(3) . 'ab');
        $this->expectException(FrameDecodingException::class);
        DataFrame::fromRaw($raw);
    }

    public function testDataFrameExactBytesRoundTrip(): void
    {
        $frame = new DataFrame(1, 'hello', true);
        $raw = $frame->toRaw();

        static::assertSame(FrameType::Data->value, $raw->type);
        static::assertSame(0x01, $raw->flags); // END_STREAM only
        static::assertSame(1, $raw->streamId);
        static::assertSame('hello', $raw->payload);

        $parsed = DataFrame::fromRaw($raw);
        static::assertSame(1, $parsed->streamId);
        static::assertSame('hello', $parsed->data);
        static::assertTrue($parsed->endStream);
    }

    public function testDataFrameNoEndStreamExactFlags(): void
    {
        $frame = new DataFrame(1, 'data', false);
        $raw = $frame->toRaw();
        static::assertSame(0x00, $raw->flags);

        $parsed = DataFrame::fromRaw($raw);
        static::assertFalse($parsed->endStream);
    }

    public function testDataFramePaddedRoundTrip(): void
    {
        // Build a padded DATA frame manually: padLength=2, data="test", padding="\x00\x00"
        $padLength = 2;
        $payload = chr($padLength) . 'test' . str_repeat("\x00", $padLength);
        $raw = new RawFrame(FrameType::Data->value, 0x09, 1, $payload); // 0x09 = PADDED | END_STREAM

        $parsed = DataFrame::fromRaw($raw);
        static::assertSame('test', $parsed->data);
        static::assertTrue($parsed->endStream);
    }

    // =========================================================================
    // GoAwayFrame (mutations 40-43)
    // =========================================================================

    public function testGoAwayFrameStreamIdIsZero(): void
    {
        $frame = new GoAwayFrame(7, ErrorCode::NoError->value, 'debug');
        static::assertSame(0, $frame->streamId);

        $raw = $frame->toRaw();
        static::assertSame(0, $raw->streamId);
    }

    public function testGoAwayFrameDebugDataExactly8BytesPayload(): void
    {
        // Exactly 8 bytes payload = lastStreamId(4) + errorCode(4) + no debug data
        // Tests the > 8 vs >= 8 boundary
        $frame = new GoAwayFrame(1, 0, '');
        $raw = $frame->toRaw();
        static::assertSame(8, strlen($raw->payload));

        $parsed = GoAwayFrame::fromRaw($raw);
        static::assertSame('', $parsed->debugData);
        static::assertSame(1, $parsed->lastStreamId);
        static::assertSame(0, $parsed->errorCode);
    }

    public function testGoAwayFrameDebugDataWith9BytesPayload(): void
    {
        // 9 bytes = 8 header bytes + 1 debug byte
        // Boundary: payloadLength > 8 is true, so debugData = substr(payload, 8) = 1 byte
        $frame = new GoAwayFrame(1, 0, 'x');
        $raw = $frame->toRaw();
        static::assertSame(9, strlen($raw->payload));

        $parsed = GoAwayFrame::fromRaw($raw);
        static::assertSame('x', $parsed->debugData);
    }

    public function testGoAwayFramePayloadLessThan8BytesFails(): void
    {
        // 7 bytes should fail
        $raw = new RawFrame(FrameType::GoAway->value, 0, 0, str_repeat("\x00", 7));
        $this->expectException(FrameDecodingException::class);
        GoAwayFrame::fromRaw($raw);
    }

    public function testGoAwayFramePayloadExactly8BytesSucceeds(): void
    {
        // 8 bytes should succeed (boundary for < 8 check)
        $raw = new RawFrame(FrameType::GoAway->value, 0, 0, pack('NN', 0, 0));
        $parsed = GoAwayFrame::fromRaw($raw);
        static::assertSame(0, $parsed->lastStreamId);
        static::assertSame(0, $parsed->errorCode);
        static::assertSame('', $parsed->debugData);
    }

    public function testGoAwayFrameFlagsByteIsZero(): void
    {
        $frame = new GoAwayFrame(0, 0, '');
        $raw = $frame->toRaw();
        static::assertSame(0, $raw->flags);
    }

    public function testGoAwayFrameExactBytesRoundTrip(): void
    {
        $frame = new GoAwayFrame(100, ErrorCode::FlowControlError->value, 'oops');
        $raw = $frame->toRaw();

        static::assertSame(FrameType::GoAway->value, $raw->type);
        static::assertSame(0, $raw->flags);
        static::assertSame(0, $raw->streamId);

        // Verify exact payload layout
        $lastStreamId = unpack('N', $raw->payload, 0)[1] & 0x7FFF_FFFF;
        $errorCode = unpack('N', $raw->payload, 4)[1];
        $debugData = substr($raw->payload, 8);

        static::assertSame(100, $lastStreamId);
        static::assertSame(ErrorCode::FlowControlError->value, $errorCode);
        static::assertSame('oops', $debugData);

        $parsed = GoAwayFrame::fromRaw($raw);
        static::assertSame(100, $parsed->lastStreamId);
        static::assertSame(ErrorCode::FlowControlError->value, $parsed->errorCode);
        static::assertSame('oops', $parsed->debugData);
    }

    // =========================================================================
    // HeadersFrame (mutations 44-67)
    // =========================================================================

    public function testHeadersFrameExclusiveDefaultIsFalse(): void
    {
        $frame = new HeadersFrame(1, 'block', false, true);
        static::assertFalse($frame->exclusive);

        $raw = $frame->toRaw();
        $parsed = HeadersFrame::fromRaw($raw);
        static::assertFalse($parsed->exclusive);
    }

    public function testHeadersFramePaddedFlagParsing(): void
    {
        // Build padded HEADERS: padLength=2, headerBlock="hdr", padding="\x00\x00"
        $padLength = 2;
        $payload = chr($padLength) . 'hdr' . str_repeat("\x00", $padLength);
        // flags: PADDED(0x08) | END_HEADERS(0x04)
        $raw = new RawFrame(FrameType::Headers->value, 0x0C, 1, $payload);

        $parsed = HeadersFrame::fromRaw($raw);
        static::assertSame('hdr', $parsed->headerBlockFragment);
        static::assertTrue($parsed->endHeaders);
        static::assertFalse($parsed->endStream);
    }

    public function testHeadersFramePaddedEmptyPayloadFails(): void
    {
        $raw = new RawFrame(FrameType::Headers->value, 0x08, 1, '');
        $this->expectException(FrameDecodingException::class);
        HeadersFrame::fromRaw($raw);
    }

    public function testHeadersFramePriorityParsing(): void
    {
        // Build HEADERS with PRIORITY: streamDep=5, weight=128, exclusive=true
        $depValue = 5 | 0x8000_0000; // exclusive bit set
        $payload = pack('NC', $depValue, 128 - 1) . 'hdr-block';
        // flags: PRIORITY(0x20) | END_HEADERS(0x04)
        $raw = new RawFrame(FrameType::Headers->value, 0x24, 1, $payload);

        $parsed = HeadersFrame::fromRaw($raw);
        static::assertTrue($parsed->exclusive);
        static::assertSame(5, $parsed->streamDependency);
        static::assertSame(128, $parsed->weight);
        static::assertSame('hdr-block', $parsed->headerBlockFragment);
    }

    public function testHeadersFramePriorityNotExclusive(): void
    {
        $depValue = 5; // no exclusive bit
        $payload = pack('NC', $depValue, 63) . 'hdr';
        $raw = new RawFrame(FrameType::Headers->value, 0x24, 1, $payload);

        $parsed = HeadersFrame::fromRaw($raw);
        static::assertFalse($parsed->exclusive);
        static::assertSame(5, $parsed->streamDependency);
        static::assertSame(64, $parsed->weight); // 63 + 1
    }

    public function testHeadersFramePriorityWeightEncoding(): void
    {
        // Weight=1 should encode as 0, weight=256 should encode as 255
        $frame1 = new HeadersFrame(1, 'h', false, true, 0, 1, false);
        $raw1 = $frame1->toRaw();
        // Priority payload: 4-byte dep + 1-byte weight
        // Weight byte should be 0 (1 - 1)
        static::assertSame(0, ord($raw1->payload[4]));

        $frame256 = new HeadersFrame(1, 'h', false, true, 0, 256, false);
        $raw256 = $frame256->toRaw();
        static::assertSame(255, ord($raw256->payload[4]));
    }

    public function testHeadersFramePriorityOffsetCalculation(): void
    {
        // PADDED + PRIORITY: padLength(1) + dep(4) + weight(1) + headerBlock + padding
        $padLength = 1;
        $depValue = 3;
        $payload = chr($padLength) . pack('NC', $depValue, 15) . 'block' . "\x00";
        // flags: PADDED(0x08) | PRIORITY(0x20) | END_HEADERS(0x04)
        $raw = new RawFrame(FrameType::Headers->value, 0x2C, 1, $payload);

        $parsed = HeadersFrame::fromRaw($raw);
        static::assertSame(3, $parsed->streamDependency);
        static::assertSame(16, $parsed->weight); // 15 + 1
        static::assertSame('block', $parsed->headerBlockFragment);
    }

    public function testHeadersFramePriorityInsufficientDataFails(): void
    {
        // PRIORITY flag set but only 3 bytes available (need 5)
        $raw = new RawFrame(FrameType::Headers->value, 0x20, 1, 'abc');
        $this->expectException(FrameDecodingException::class);
        HeadersFrame::fromRaw($raw);
    }

    public function testHeadersFrameFlagsEncoding(): void
    {
        // endStream=true, endHeaders=true, no priority
        $frame = new HeadersFrame(1, 'h', true, true);
        $raw = $frame->toRaw();
        static::assertSame(0x05, $raw->flags); // 0x01 | 0x04

        // endStream=false, endHeaders=true, no priority
        $frame2 = new HeadersFrame(1, 'h', false, true);
        $raw2 = $frame2->toRaw();
        static::assertSame(0x04, $raw2->flags);

        // endStream=true, endHeaders=false, no priority
        $frame3 = new HeadersFrame(1, 'h', true, false);
        $raw3 = $frame3->toRaw();
        static::assertSame(0x01, $raw3->flags);

        // endStream=false, endHeaders=false, no priority
        $frame4 = new HeadersFrame(1, 'h', false, false);
        $raw4 = $frame4->toRaw();
        static::assertSame(0x00, $raw4->flags);
    }

    public function testHeadersFrameFlagsEncodingWithPriority(): void
    {
        // endStream=true, endHeaders=true, priority
        $frame = new HeadersFrame(1, 'h', true, true, 0, 16, false);
        $raw = $frame->toRaw();
        static::assertSame(0x25, $raw->flags); // 0x01 | 0x04 | 0x20

        // endStream=false, endHeaders=false, priority
        $frame2 = new HeadersFrame(1, 'h', false, false, 0, 16, false);
        $raw2 = $frame2->toRaw();
        static::assertSame(0x20, $raw2->flags);
    }

    public function testHeadersFrameWeightDefault16(): void
    {
        // When weight is null but streamDependency is set, weight defaults to 16
        $frame = new HeadersFrame(1, 'hdr', false, true, 0, null, false);
        $raw = $frame->toRaw();

        // Priority payload present (streamDependency is not null)
        // Weight byte should be 15 (16 - 1)
        static::assertSame(15, ord($raw->payload[4]));

        $parsed = HeadersFrame::fromRaw($raw);
        static::assertSame(16, $parsed->weight);
    }

    public function testHeadersFramePayloadConcatenationOrder(): void
    {
        // With priority: payload = pack(dep, weight) + headerBlockFragment
        $frame = new HeadersFrame(1, 'ABCD', false, true, 0, 16, false);
        $raw = $frame->toRaw();

        // First 5 bytes: 4-byte dep + 1-byte weight, then header block
        static::assertSame('ABCD', substr($raw->payload, 5));

        // Without priority: payload = headerBlockFragment directly
        $frame2 = new HeadersFrame(1, 'EFGH', false, true);
        $raw2 = $frame2->toRaw();
        static::assertSame('EFGH', $raw2->payload);
    }

    public function testHeadersFrameExclusiveBitInToRaw(): void
    {
        $frame = new HeadersFrame(1, 'h', false, true, 10, 32, true);
        $raw = $frame->toRaw();

        $depValue = unpack('N', $raw->payload, 0)[1];
        // Exclusive bit should be set
        static::assertNotSame(0, $depValue & 0x8000_0000);
        // Stream dependency should be 10
        static::assertSame(10, $depValue & 0x7FFF_FFFF);

        // Without exclusive
        $frame2 = new HeadersFrame(1, 'h', false, true, 10, 32, false);
        $raw2 = $frame2->toRaw();
        $depValue2 = unpack('N', $raw2->payload, 0)[1];
        static::assertSame(0, $depValue2 & 0x8000_0000);
        static::assertSame(10, $depValue2 & 0x7FFF_FFFF);
    }

    public function testHeadersFramePaddingDataLengthNegativeFails(): void
    {
        // padLength that makes dataLength negative after offset adjustment
        // PADDED flag, padLength=10, but only 5 bytes total
        $raw = new RawFrame(FrameType::Headers->value, 0x0C, 1, chr(10) . 'abcd');
        $this->expectException(FrameDecodingException::class);
        HeadersFrame::fromRaw($raw);
    }

    // =========================================================================
    // OriginFrame (mutations 68-74)
    // =========================================================================

    public function testOriginFrameStreamIdIsZero(): void
    {
        $frame = new OriginFrame(['https://example.com']);
        static::assertSame(0, $frame->streamId);

        $raw = $frame->toRaw();
        static::assertSame(0, $raw->streamId);
    }

    public function testOriginFrameTruncatedOriginLengthFails(): void
    {
        // Payload with 1 byte left (need 2 for origin length)
        $raw = new RawFrame(FrameType::Origin->value, 0, 0, "\x00");
        $this->expectException(FrameDecodingException::class);
        OriginFrame::fromRaw($raw);
    }

    public function testOriginFrameTruncatedOriginValueFails(): void
    {
        // Origin length says 10, but only 3 bytes available after length field
        $raw = new RawFrame(FrameType::Origin->value, 0, 0, pack('n', 10) . 'abc');
        $this->expectException(FrameDecodingException::class);
        OriginFrame::fromRaw($raw);
    }

    public function testOriginFrameOriginLengthZeroFiltered(): void
    {
        // An origin entry with length 0 should be skipped (originLength > 0 filter)
        $payload = pack('n', 0); // one entry with length 0
        $raw = new RawFrame(FrameType::Origin->value, 0, 0, $payload);
        $parsed = OriginFrame::fromRaw($raw);
        static::assertSame([], $parsed->origins);
    }

    public function testOriginFrameFlagsByteIsZero(): void
    {
        $frame = new OriginFrame(['https://a.com']);
        $raw = $frame->toRaw();
        static::assertSame(0, $raw->flags);
    }

    public function testOriginFrameExactBytesRoundTrip(): void
    {
        $origins = ['https://example.com', 'https://cdn.example.com'];
        $frame = new OriginFrame($origins);
        $raw = $frame->toRaw();

        static::assertSame(FrameType::Origin->value, $raw->type);
        static::assertSame(0, $raw->flags);
        static::assertSame(0, $raw->streamId);

        // Verify payload layout: repeated [2-byte length + origin]
        $offset = 0;
        foreach ($origins as $origin) {
            $len = unpack('n', $raw->payload, $offset)[1];
            static::assertSame(strlen($origin), $len);
            static::assertSame($origin, substr($raw->payload, $offset + 2, $len));
            $offset += 2 + $len;
        }

        static::assertSame(strlen($raw->payload), $offset);

        $parsed = OriginFrame::fromRaw($raw);
        static::assertSame($origins, $parsed->origins);
    }

    public function testOriginFrameMultipleOriginsWithZeroLengthEntry(): void
    {
        // Mix of real and zero-length origins
        $payload = pack('n', 5) . 'a.com' . pack('n', 0) . pack('n', 5) . 'b.com';
        $raw = new RawFrame(FrameType::Origin->value, 0, 0, $payload);
        $parsed = OriginFrame::fromRaw($raw);
        static::assertSame(['a.com', 'b.com'], $parsed->origins);
    }

    public function testOriginFrameEmptyPayloadSucceeds(): void
    {
        $raw = new RawFrame(FrameType::Origin->value, 0, 0, '');
        $parsed = OriginFrame::fromRaw($raw);
        static::assertSame([], $parsed->origins);
    }

    // =========================================================================
    // PingFrame (mutation 75)
    // =========================================================================

    public function testPingFrameStreamIdIsZero(): void
    {
        $frame = new PingFrame('12345678', false);
        static::assertSame(0, $frame->streamId);

        $raw = $frame->toRaw();
        static::assertSame(0, $raw->streamId);

        // Also verify for ACK
        $ackFrame = new PingFrame('abcdefgh', true);
        $rawAck = $ackFrame->toRaw();
        static::assertSame(0, $rawAck->streamId);
    }

    public function testPingFrameExactBytesRoundTrip(): void
    {
        $frame = new PingFrame('ABCDEFGH', false);
        $raw = $frame->toRaw();

        static::assertSame(FrameType::Ping->value, $raw->type);
        static::assertSame(0x00, $raw->flags);
        static::assertSame(0, $raw->streamId);
        static::assertSame('ABCDEFGH', $raw->payload);

        $parsed = PingFrame::fromRaw($raw);
        static::assertSame('ABCDEFGH', $parsed->opaqueData);
        static::assertFalse($parsed->ack);
    }

    public function testPingFrameAckExactFlags(): void
    {
        $frame = new PingFrame('12345678', true);
        $raw = $frame->toRaw();
        static::assertSame(0x01, $raw->flags);

        $parsed = PingFrame::fromRaw($raw);
        static::assertTrue($parsed->ack);
    }

    // =========================================================================
    // PushPromiseFrame (mutations 76-89)
    // =========================================================================

    public function testPushPromiseFrameEndHeadersFlagValue(): void
    {
        // END_HEADERS is 0x04
        $frame = new PushPromiseFrame(1, 2, 'headers', true);
        $raw = $frame->toRaw();
        static::assertSame(0x04, $raw->flags);

        $frame2 = new PushPromiseFrame(1, 2, 'headers', false);
        $raw2 = $frame2->toRaw();
        static::assertSame(0x00, $raw2->flags);
    }

    public function testPushPromiseFramePaddedFlagParsing(): void
    {
        // Build padded PUSH_PROMISE
        $padLength = 2;
        $promisedStreamId = pack('N', 4);
        $payload = chr($padLength) . $promisedStreamId . 'hdr' . str_repeat("\x00", $padLength);
        // flags: PADDED(0x08) | END_HEADERS(0x04)
        $raw = new RawFrame(FrameType::PushPromise->value, 0x0C, 1, $payload);

        $parsed = PushPromiseFrame::fromRaw($raw);
        static::assertSame(4, $parsed->promisedStreamId);
        static::assertSame('hdr', $parsed->headerBlockFragment);
        static::assertTrue($parsed->endHeaders);
    }

    public function testPushPromiseFramePaddedEmptyPayloadFails(): void
    {
        $raw = new RawFrame(FrameType::PushPromise->value, 0x08, 1, '');
        $this->expectException(FrameDecodingException::class);
        PushPromiseFrame::fromRaw($raw);
    }

    public function testPushPromiseFramePaddingLengthCalculation(): void
    {
        // padLength that makes dataLength < 0
        $payload = chr(20) . pack('N', 2) . 'x';
        $raw = new RawFrame(FrameType::PushPromise->value, 0x0C, 1, $payload);
        $this->expectException(FrameDecodingException::class);
        PushPromiseFrame::fromRaw($raw);
    }

    public function testPushPromiseFramePromisedStreamIdMask(): void
    {
        // High bit should be masked off (0x7FFFFFFF)
        $promisedStreamId = 0x8000_0002; // high bit set + stream 2
        $payload = pack('N', $promisedStreamId) . 'headers';
        $raw = new RawFrame(FrameType::PushPromise->value, 0x04, 1, $payload);

        $parsed = PushPromiseFrame::fromRaw($raw);
        static::assertSame(2, $parsed->promisedStreamId);
    }

    public function testPushPromiseFrameOffsetCalculationsNoPadding(): void
    {
        // Without padding: offset starts at 0, then +4 for promisedStreamId
        $payload = pack('N', 3) . 'fragment';
        $raw = new RawFrame(FrameType::PushPromise->value, 0x04, 1, $payload);

        $parsed = PushPromiseFrame::fromRaw($raw);
        static::assertSame(3, $parsed->promisedStreamId);
        static::assertSame('fragment', $parsed->headerBlockFragment);
    }

    public function testPushPromiseFrameToRawFlags(): void
    {
        $frame = new PushPromiseFrame(1, 2, 'h', true);
        $raw = $frame->toRaw();
        static::assertSame(0x04, $raw->flags);

        $frame2 = new PushPromiseFrame(1, 2, 'h', false);
        $raw2 = $frame2->toRaw();
        static::assertSame(0x00, $raw2->flags);
    }

    public function testPushPromiseFrameToRawStreamId(): void
    {
        $frame = new PushPromiseFrame(7, 10, 'h', true);
        $raw = $frame->toRaw();
        static::assertSame(7, $raw->streamId);
    }

    public function testPushPromiseFrameExactBytesRoundTrip(): void
    {
        $frame = new PushPromiseFrame(1, 2, 'promise-data', true);
        $raw = $frame->toRaw();

        static::assertSame(FrameType::PushPromise->value, $raw->type);
        static::assertSame(0x04, $raw->flags);
        static::assertSame(1, $raw->streamId);

        // Payload: 4-byte promisedStreamId + headerBlockFragment
        $promisedId = unpack('N', $raw->payload, 0)[1] & 0x7FFF_FFFF;
        static::assertSame(2, $promisedId);
        static::assertSame('promise-data', substr($raw->payload, 4));

        $parsed = PushPromiseFrame::fromRaw($raw);
        static::assertSame(1, $parsed->streamId);
        static::assertSame(2, $parsed->promisedStreamId);
        static::assertSame('promise-data', $parsed->headerBlockFragment);
        static::assertTrue($parsed->endHeaders);
    }

    public function testPushPromiseFramePromisedStreamIdMaskInToRaw(): void
    {
        // toRaw should mask with 0x7FFFFFFF
        $frame = new PushPromiseFrame(1, 2, 'h', true);
        $raw = $frame->toRaw();

        $rawPromisedId = unpack('N', $raw->payload, 0)[1];
        // Should not have high bit set
        static::assertSame(0, $rawPromisedId & 0x8000_0000);
        static::assertSame(2, $rawPromisedId & 0x7FFF_FFFF);
    }

    public function testPushPromiseFrameMissingPromisedStreamIdFails(): void
    {
        // Payload too short for promised stream ID (need 4 bytes)
        $raw = new RawFrame(FrameType::PushPromise->value, 0x04, 1, 'ab');
        $this->expectException(FrameDecodingException::class);
        PushPromiseFrame::fromRaw($raw);
    }

    // =========================================================================
    // RstStreamFrame (mutations 90-91)
    // =========================================================================

    public function testRstStreamFrameFlagsByteIsZero(): void
    {
        $frame = new RstStreamFrame(1, ErrorCode::Cancel);
        $raw = $frame->toRaw();
        static::assertSame(0, $raw->flags);
    }

    public function testRstStreamFrameExactBytesRoundTrip(): void
    {
        $frame = new RstStreamFrame(5, ErrorCode::ProtocolError);
        $raw = $frame->toRaw();

        static::assertSame(FrameType::RstStream->value, $raw->type);
        static::assertSame(0, $raw->flags);
        static::assertSame(5, $raw->streamId);
        static::assertSame(4, strlen($raw->payload));

        $errorCodeValue = unpack('N', $raw->payload, 0)[1];
        static::assertSame(ErrorCode::ProtocolError->value, $errorCodeValue);

        $parsed = RstStreamFrame::fromRaw($raw);
        static::assertSame(5, $parsed->streamId);
        static::assertSame(ErrorCode::ProtocolError, $parsed->errorCode);
    }

    // =========================================================================
    // SettingsFrame (mutations 92-93)
    // =========================================================================

    public function testSettingsFrameStreamIdIsZero(): void
    {
        $frame = new SettingsFrame([0x3 => 100], false);
        static::assertSame(0, $frame->streamId);

        $raw = $frame->toRaw();
        static::assertSame(0, $raw->streamId);
    }

    public function testSettingsFrameAckWithNonEmptyPayloadThrows(): void
    {
        // ACK flag with non-empty payload should throw
        $payload = pack('nN', 0x3, 100);
        $raw = new RawFrame(FrameType::Settings->value, 0x01, 0, $payload);
        $this->expectException(FrameDecodingException::class);
        SettingsFrame::fromRaw($raw);
    }

    public function testSettingsFrameAckWithEmptyPayloadSucceeds(): void
    {
        $raw = new RawFrame(FrameType::Settings->value, 0x01, 0, '');
        $parsed = SettingsFrame::fromRaw($raw);
        static::assertTrue($parsed->ack);
        static::assertSame([], $parsed->settings);
    }

    public function testSettingsFrameExactBytesRoundTrip(): void
    {
        $frame = new SettingsFrame([0x1 => 4096, 0x3 => 100], false);
        $raw = $frame->toRaw();

        static::assertSame(FrameType::Settings->value, $raw->type);
        static::assertSame(0, $raw->flags);
        static::assertSame(0, $raw->streamId);
        // 2 settings * 6 bytes each = 12 bytes
        static::assertSame(12, strlen($raw->payload));

        $parsed = SettingsFrame::fromRaw($raw);
        static::assertFalse($parsed->ack);
        static::assertSame(4096, $parsed->settings[0x1]);
        static::assertSame(100, $parsed->settings[0x3]);
    }

    public function testSettingsFrameAckFlagsEncoding(): void
    {
        $frame = new SettingsFrame([], true);
        $raw = $frame->toRaw();
        static::assertSame(0x01, $raw->flags);

        $frame2 = new SettingsFrame([], false);
        $raw2 = $frame2->toRaw();
        static::assertSame(0x00, $raw2->flags);
    }

    // =========================================================================
    // WindowUpdateFrame (mutations 94-99)
    // =========================================================================

    public function testWindowUpdateFrameIncrementMaskValue(): void
    {
        // The high bit should be masked off in fromRaw (0x7FFFFFFF)
        $payload = pack('N', 0x8000_0400); // high bit set + 1024
        $raw = new RawFrame(FrameType::WindowUpdate->value, 0, 1, $payload);
        $parsed = WindowUpdateFrame::fromRaw($raw);
        static::assertSame(1024, $parsed->windowSizeIncrement);
    }

    public function testWindowUpdateFrameZeroIncrementFails(): void
    {
        // After masking, increment = 0 should throw
        $payload = pack('N', 0x8000_0000); // high bit set, increment = 0
        $raw = new RawFrame(FrameType::WindowUpdate->value, 0, 1, $payload);
        $this->expectException(FrameDecodingException::class);
        WindowUpdateFrame::fromRaw($raw);
    }

    public function testWindowUpdateFrameFlagsByteIsZero(): void
    {
        $frame = new WindowUpdateFrame(1, 1024);
        $raw = $frame->toRaw();
        static::assertSame(0, $raw->flags);
    }

    public function testWindowUpdateFrameIncrementMaskInToRaw(): void
    {
        $frame = new WindowUpdateFrame(1, 1024);
        $raw = $frame->toRaw();

        // Verify the raw payload has the increment with high bit cleared
        $rawValue = unpack('N', $raw->payload, 0)[1];
        static::assertSame(0, $rawValue & 0x8000_0000);
        static::assertSame(1024, $rawValue & 0x7FFF_FFFF);
    }

    public function testWindowUpdateFrameExactBytesRoundTrip(): void
    {
        $frame = new WindowUpdateFrame(3, 65_535);
        $raw = $frame->toRaw();

        static::assertSame(FrameType::WindowUpdate->value, $raw->type);
        static::assertSame(0, $raw->flags);
        static::assertSame(3, $raw->streamId);
        static::assertSame(4, strlen($raw->payload));

        $increment = unpack('N', $raw->payload, 0)[1] & 0x7FFF_FFFF;
        static::assertSame(65_535, $increment);

        $parsed = WindowUpdateFrame::fromRaw($raw);
        static::assertSame(3, $parsed->streamId);
        static::assertSame(65_535, $parsed->windowSizeIncrement);
    }

    public function testWindowUpdateFrameMaxIncrement(): void
    {
        $maxIncrement = 0x7FFF_FFFF; // 2147483647
        $frame = new WindowUpdateFrame(0, $maxIncrement);
        $raw = $frame->toRaw();

        $parsed = WindowUpdateFrame::fromRaw($raw);
        static::assertSame($maxIncrement, $parsed->windowSizeIncrement);
    }

    public function testWindowUpdateFrameConnectionLevel(): void
    {
        // StreamId 0 is valid for WindowUpdate (connection-level)
        $frame = new WindowUpdateFrame(0, 512);
        $raw = $frame->toRaw();
        static::assertSame(0, $raw->streamId);

        $parsed = WindowUpdateFrame::fromRaw($raw);
        static::assertSame(0, $parsed->streamId);
        static::assertSame(512, $parsed->windowSizeIncrement);
    }
}
