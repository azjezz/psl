<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\ErrorCode;
use Psl\H2\Event\StreamClosed;
use Psl\H2\Event\StreamReset;
use Psl\H2\Exception\StreamException;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Frame\RstStreamFrame;
use Psl\H2\Internal\StateMachine;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

final class RstStreamTest extends TestCase
{
    public function testSendRstStream(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'GET')]);

        $frames = $sm->resetStream($streamId, ErrorCode::Cancel);

        static::assertCount(1, $frames);
        static::assertSame(FrameType::RstStream->value, $frames[0]->type);
    }

    public function testReceiveRstStream(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $rstRaw = new RstStreamFrame(1, ErrorCode::Cancel)->toRaw();
        [, $events] = $sm->receive($rstRaw);

        static::assertCount(2, $events);
        static::assertInstanceOf(StreamReset::class, $events[0]);
        static::assertSame(1, $events[0]->streamId);
        static::assertSame(ErrorCode::Cancel, $events[0]->errorCode);
        static::assertInstanceOf(StreamClosed::class, $events[1]);
    }

    public function testSendDataAfterResetStreamThrows(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);
        $sm->resetStream($streamId, ErrorCode::Cancel);

        $this->expectException(StreamException::class);

        $sm->sendData($streamId, 'data', false);
    }
}
