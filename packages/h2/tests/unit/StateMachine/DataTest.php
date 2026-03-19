<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\Event\DataReceived;
use Psl\H2\Event\StreamClosed;
use Psl\H2\Exception\StreamException;
use Psl\H2\Frame\DataFrame;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Internal\StateMachine;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

final class DataTest extends TestCase
{
    /**
     * @return array{StateMachine, Encoder}
     */
    private function createServerWithOpenStream(): array
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'POST'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $raw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($raw);

        return [$sm, $encoder];
    }

    public function testReceiveData(): void
    {
        [$sm] = $this->createServerWithOpenStream();

        $dataRaw = new DataFrame(1, 'hello', false)->toRaw();
        [$responseFrames, $events] = $sm->receive($dataRaw);

        static::assertCount(1, $events);
        static::assertInstanceOf(DataReceived::class, $events[0]);
        static::assertSame(1, $events[0]->streamId);
        static::assertSame('hello', $events[0]->data);
        static::assertFalse($events[0]->endStream);

        $windowUpdateCount = 0;
        foreach ($responseFrames as $frame) {
            if ($frame->type !== FrameType::WindowUpdate->value) {
                continue;
            }

            $windowUpdateCount++;
        }

        static::assertSame(2, $windowUpdateCount);
    }

    public function testReceiveDataEndStream(): void
    {
        [$sm] = $this->createServerWithOpenStream();

        $dataRaw = new DataFrame(1, 'done', true)->toRaw();
        [, $events] = $sm->receive($dataRaw);

        static::assertCount(1, $events);
        static::assertInstanceOf(DataReceived::class, $events[0]);
        static::assertTrue($events[0]->endStream);
    }

    public function testSendData(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $frames = $sm->sendData($streamId, 'body', false);

        static::assertCount(1, $frames);
        static::assertSame(FrameType::Data->value, $frames[0]->type);
    }

    public function testSendDataEndStream(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $frames = $sm->sendData($streamId, 'end', true);
        static::assertSame(0x01, $frames[0]->flags & 0x01);
    }

    public function testSendEmptyData(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $frames = $sm->sendData($streamId, '', true);
        static::assertCount(1, $frames);
    }

    public function testSendDataEndStreamTransitionsOpenToHalfClosedLocal(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);
        $sm->sendData($streamId, 'body', true);

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('HalfClosedLocal');

        $sm->sendData($streamId, 'more', false);
    }

    public function testSendDataEndStreamOnHalfClosedRemoteTransitionsToClosed(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'POST'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, true, true)->toRaw();
        $sm->receive($headersRaw);

        $sm->sendHeadersEncoded(1, [new Header(':status', '200')]);
        $sm->sendData(1, 'response', true);

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('closed');

        $sm->sendData(1, 'after-close');
    }

    public function testReceiveDataEndStreamOnOpenEmitsDataOnly(): void
    {
        [$sm] = $this->createServerWithOpenStream();

        $dataRaw = new DataFrame(1, 'final', true)->toRaw();
        [, $events] = $sm->receive($dataRaw);

        static::assertCount(1, $events);
        static::assertInstanceOf(DataReceived::class, $events[0]);
        static::assertTrue($events[0]->endStream);
    }

    public function testReceiveDataEndStreamOnHalfClosedLocalEmitsStreamClosed(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'POST'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $sm->sendHeadersEncoded(1, [new Header(':status', '200')], true);

        $dataRaw = new DataFrame(1, 'done', true)->toRaw();
        [, $events] = $sm->receive($dataRaw);

        static::assertCount(2, $events);
        static::assertInstanceOf(DataReceived::class, $events[0]);
        static::assertInstanceOf(StreamClosed::class, $events[1]);
    }

    public function testSendDataOnNeverOpenedStreamThrows(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('closed');

        $sm->sendData(99, 'data');
    }

    public function testSendDataOnHalfClosedLocalShowsStateName(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);
        $sm->sendData($streamId, 'body', true);

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('HalfClosedLocal');

        $sm->sendData($streamId, 'more');
    }

    public function testReceiveDataOnHalfClosedRemoteThrowsWithStateName(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, true, true)->toRaw();
        $sm->receive($headersRaw);

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('HalfClosedRemote');

        $dataRaw = new DataFrame(1, 'data', false)->toRaw();
        $sm->receive($dataRaw);
    }

    public function testReceiveDataEndStreamOnHalfClosedLocalFullyClosesStream(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'POST'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $sm->sendHeadersEncoded(1, [new Header(':status', '200')], true);

        $dataRaw = new DataFrame(1, 'done', true)->toRaw();
        $sm->receive($dataRaw);

        $this->expectException(StreamException::class);
        $this->expectExceptionMessage('closed');

        $sm->sendHeadersEncoded(1, [new Header('x-trailer', 'val')]);
    }
}
