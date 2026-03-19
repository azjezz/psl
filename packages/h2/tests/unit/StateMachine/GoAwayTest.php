<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\ErrorCode;
use Psl\H2\Event\GoAwayReceived;
use Psl\H2\Frame;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\GoAwayFrame;
use Psl\H2\Internal\StateMachine;

final class GoAwayTest extends TestCase
{
    public function testSendGoAway(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $frames = $sm->goAway(ErrorCode::NoError, 'graceful');

        static::assertCount(1, $frames);
        static::assertSame(FrameType::GoAway->value, $frames[0]->type);
        static::assertTrue($sm->shutdown);
    }

    public function testReceiveGoAway(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $goAwayRaw = new GoAwayFrame(5, ErrorCode::NoError->value, 'bye')->toRaw();
        [, $events] = $sm->receive($goAwayRaw);

        static::assertCount(1, $events);
        $event = $events[0];
        static::assertInstanceOf(GoAwayReceived::class, $event);
        static::assertSame(5, $event->lastStreamId);
        static::assertSame(ErrorCode::NoError, $event->errorCode);
        static::assertSame('bye', $event->debugData);
        static::assertTrue($sm->shutdown);
    }

    public function testGoAwayWithDebugData(): void
    {
        $sm = new StateMachine(false);

        $frames = $sm->goAway(ErrorCode::ProtocolError, 'invalid frame');
        $parsed = GoAwayFrame::fromRaw($frames[0]);

        static::assertSame(ErrorCode::ProtocolError->value, $parsed->errorCode);
        static::assertSame('invalid frame', $parsed->debugData);
    }

    public function testGoAwayWithCustomLastStreamId(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $frames = $sm->goAway(ErrorCode::NoError, '', 42);

        $parsed = GoAwayFrame::fromRaw($frames[0]);
        static::assertSame(42, $parsed->lastStreamId);
    }

    public function testReceiveGoAwayWithUnknownErrorCode(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $goAwayRaw = new GoAwayFrame(0, 0xFF, 'unknown error')->toRaw();
        [, $events] = $sm->receive($goAwayRaw);

        static::assertCount(1, $events);
        $event = $events[0];
        static::assertInstanceOf(GoAwayReceived::class, $event);
        static::assertSame(ErrorCode::InternalError, $event->errorCode);
    }

    public function testIsShutdownFalseByDefault(): void
    {
        $sm = new StateMachine(true);

        static::assertFalse($sm->shutdown);
    }
}
