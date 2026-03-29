<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\Event\PingReceived;
use Psl\H2\Frame;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\PingFrame;
use Psl\H2\Internal\StateMachine;

use function strlen;

final class PingTest extends TestCase
{
    public function testSendPing(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $frames = $sm->ping('12345678');

        static::assertCount(1, $frames);
        static::assertSame(FrameType::Ping->value, $frames[0]->type);
        static::assertSame(0, $frames[0]->flags & 0x01);
    }

    public function testSendPingPadsShortData(): void
    {
        $sm = new StateMachine(true);

        $frames = $sm->ping('hi');
        $parsed = PingFrame::fromRaw($frames[0]);
        static::assertSame(8, strlen($parsed->opaqueData));
    }

    public function testSendPingTruncatesLongData(): void
    {
        $sm = new StateMachine(true);

        $frames = $sm->ping('1234567890');
        $parsed = PingFrame::fromRaw($frames[0]);
        static::assertSame('12345678', $parsed->opaqueData);
    }

    public function testReceivePingReturnsAck(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $pingRaw = new PingFrame('abcdefgh', false)->toRaw();
        [$responseFrames, $events] = $sm->receive($pingRaw);

        static::assertCount(1, $responseFrames);
        $encoded = $responseFrames[0];
        static::assertIsString($encoded);
        static::assertSame(17, strlen($encoded));
        [$ackFrame] = Frame\decode($encoded);
        static::assertSame(FrameType::Ping->value, $ackFrame->type);
        static::assertSame(0x01, $ackFrame->flags & 0x01);

        static::assertCount(1, $events);
        static::assertInstanceOf(PingReceived::class, $events[0]);
        static::assertSame('abcdefgh', $events[0]->opaqueData);
        static::assertFalse($events[0]->ack);
    }

    public function testReceivePingAck(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $pingAckRaw = new PingFrame('response', true)->toRaw();
        [$responseFrames, $events] = $sm->receive($pingAckRaw);

        static::assertSame([], $responseFrames);
        static::assertCount(1, $events);
        static::assertInstanceOf(PingReceived::class, $events[0]);
        static::assertTrue($events[0]->ack);
    }

    public function testSendPingExactly8BytesIsUnchanged(): void
    {
        $sm = new StateMachine(true);

        $frames = $sm->ping('ABCDEFGH');
        $parsed = PingFrame::fromRaw($frames[0]);
        static::assertSame('ABCDEFGH', $parsed->opaqueData);
        static::assertSame(8, strlen($parsed->opaqueData));
    }
}
