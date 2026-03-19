<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Frame;

use PHPUnit\Framework\TestCase;
use Psl\H2\ErrorCode;
use Psl\H2\Frame;
use Psl\H2\Frame\ContinuationFrame;
use Psl\H2\Frame\DataFrame;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\GoAwayFrame;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Frame\PingFrame;
use Psl\H2\Frame\PriorityFrame;
use Psl\H2\Frame\PushPromiseFrame;
use Psl\H2\Frame\RawFrame;
use Psl\H2\Frame\RstStreamFrame;
use Psl\H2\Frame\SettingsFrame;
use Psl\H2\Frame\WindowUpdateFrame;

final class EncodeDecodeTest extends TestCase
{
    public function testDataFrameRoundTrip(): void
    {
        $frame = new DataFrame(1, 'hello', false);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $offset] = Frame\decode($encoded);

        static::assertSame(FrameType::Data->value, $decoded->type);
        static::assertSame(1, $decoded->streamId);
        static::assertSame('hello', $decoded->payload);
        static::assertSame(14, $offset);
    }

    public function testDataFrameEndStream(): void
    {
        $frame = new DataFrame(1, 'data', true);
        $raw = $frame->toRaw();

        static::assertSame(0x01, $raw->flags & 0x01);
    }

    public function testHeadersFrameRoundTrip(): void
    {
        $frame = new HeadersFrame(3, 'block', false, true);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = HeadersFrame::fromRaw($decoded);

        static::assertSame(3, $parsed->streamId);
        static::assertSame('block', $parsed->headerBlockFragment);
        static::assertTrue($parsed->endHeaders);
        static::assertFalse($parsed->endStream);
    }

    public function testHeadersFrameWithPriority(): void
    {
        $frame = new HeadersFrame(1, 'hdr', true, true, 0, 16, true);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = HeadersFrame::fromRaw($decoded);

        static::assertTrue($parsed->endStream);
        static::assertTrue($parsed->exclusive);
        static::assertSame(0, $parsed->streamDependency);
        static::assertSame(16, $parsed->weight);
    }

    public function testPriorityFrameRoundTrip(): void
    {
        $frame = new PriorityFrame(3, 1, 128, false);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = PriorityFrame::fromRaw($decoded);

        static::assertSame(3, $parsed->streamId);
        static::assertSame(1, $parsed->streamDependency);
        static::assertSame(128, $parsed->weight);
        static::assertFalse($parsed->exclusive);
    }

    public function testRstStreamFrameRoundTrip(): void
    {
        $frame = new RstStreamFrame(5, ErrorCode::Cancel);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = RstStreamFrame::fromRaw($decoded);

        static::assertSame(5, $parsed->streamId);
        static::assertSame(ErrorCode::Cancel, $parsed->errorCode);
    }

    public function testSettingsFrameRoundTrip(): void
    {
        $frame = new SettingsFrame([0x3 => 100, 0x4 => 32_768], false);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = SettingsFrame::fromRaw($decoded);

        static::assertFalse($parsed->ack);
        static::assertSame(100, $parsed->settings[0x3]);
        static::assertSame(32_768, $parsed->settings[0x4]);
    }

    public function testSettingsAckRoundTrip(): void
    {
        $frame = new SettingsFrame([], true);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = SettingsFrame::fromRaw($decoded);

        static::assertTrue($parsed->ack);
        static::assertSame([], $parsed->settings);
    }

    public function testPushPromiseFrameRoundTrip(): void
    {
        $frame = new PushPromiseFrame(1, 2, 'promise', true);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = PushPromiseFrame::fromRaw($decoded);

        static::assertSame(1, $parsed->streamId);
        static::assertSame(2, $parsed->promisedStreamId);
        static::assertSame('promise', $parsed->headerBlockFragment);
        static::assertTrue($parsed->endHeaders);
    }

    public function testPingFrameRoundTrip(): void
    {
        $data = '12345678';
        $frame = new PingFrame($data, false);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = PingFrame::fromRaw($decoded);

        static::assertSame($data, $parsed->opaqueData);
        static::assertFalse($parsed->ack);
    }

    public function testPingAckRoundTrip(): void
    {
        $data = 'abcdefgh';
        $frame = new PingFrame($data, true);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = PingFrame::fromRaw($decoded);

        static::assertTrue($parsed->ack);
    }

    public function testGoAwayFrameRoundTrip(): void
    {
        $frame = new GoAwayFrame(7, ErrorCode::NoError->value, 'bye');
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = GoAwayFrame::fromRaw($decoded);

        static::assertSame(7, $parsed->lastStreamId);
        static::assertSame(ErrorCode::NoError->value, $parsed->errorCode);
        static::assertSame('bye', $parsed->debugData);
    }

    public function testWindowUpdateFrameRoundTrip(): void
    {
        $frame = new WindowUpdateFrame(1, 1024);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = WindowUpdateFrame::fromRaw($decoded);

        static::assertSame(1, $parsed->streamId);
        static::assertSame(1024, $parsed->windowSizeIncrement);
    }

    public function testContinuationFrameRoundTrip(): void
    {
        $frame = new ContinuationFrame(1, 'more-headers', true);
        $raw = $frame->toRaw();
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);
        $parsed = ContinuationFrame::fromRaw($decoded);

        static::assertSame(1, $parsed->streamId);
        static::assertSame('more-headers', $parsed->headerBlockFragment);
        static::assertTrue($parsed->endHeaders);
    }

    public function testMultipleFramesInSequence(): void
    {
        $frame1 = new DataFrame(1, 'a', false)->toRaw();
        $frame2 = new PingFrame('12345678', false)->toRaw();

        $data = Frame\encode($frame1) . Frame\encode($frame2);

        [$decoded1, $offset] = Frame\decode($data, 0);
        [$decoded2, $_] = Frame\decode($data, $offset);

        static::assertSame(FrameType::Data->value, $decoded1->type);
        static::assertSame(FrameType::Ping->value, $decoded2->type);
    }

    public function testEmptyPayloadFrame(): void
    {
        $raw = new RawFrame(FrameType::Settings->value, 0x01, 0, '');
        $encoded = Frame\encode($raw);
        [$decoded, $_] = Frame\decode($encoded);

        static::assertSame('', $decoded->payload);
        static::assertSame(0x01, $decoded->flags);
    }
}
