<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\Event\StreamClosed;
use Psl\H2\Exception\FlowControlException;
use Psl\H2\Frame;
use Psl\H2\Frame\DataFrame;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Frame\WindowUpdateFrame;
use Psl\H2\Internal\StateMachine;
use Psl\H2\Setting;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

use function str_repeat;
use function strlen;

final class FlowControlTest extends TestCase
{
    public function testInitialSendWindow(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'GET')]);

        static::assertSame(65_535, $sm->availableSendWindow($streamId));
    }

    public function testSendDataConsumesWindow(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $before = $sm->availableSendWindow($streamId);
        $sm->sendData($streamId, str_repeat('x', 1000));
        $after = $sm->availableSendWindow($streamId);

        static::assertSame(1000, $before - $after);
    }

    public function testWindowExhausted(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $sm->sendData($streamId, str_repeat('x', 65_535));

        $this->expectException(FlowControlException::class);
        $sm->sendData($streamId, 'one more byte');
    }

    public function testStreamWindowExhaustedWhileConnectionWindowAvailable(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $sm->receive(new WindowUpdateFrame(0, 100_000)->toRaw());

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $sm->sendData($streamId, str_repeat('x', 65_535));

        $this->expectException(FlowControlException::class);
        $sm->sendData($streamId, 'x');
    }

    public function testAvailableSendWindowForUnknownStream(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $window = $sm->availableSendWindow(999);
        static::assertSame(65_535, $window);
    }

    public function testConnectionWindowUpdateIncreasesAvailableWindow(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        /** @var non-negative-int $streamId */
        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);
        $sm->sendData($streamId, str_repeat('x', 65_535));

        $sm->receive(new WindowUpdateFrame($streamId, 500)->toRaw());

        $windowBefore = $sm->availableSendWindow($streamId);
        static::assertSame(0, $windowBefore);

        $sm->receive(new WindowUpdateFrame(0, 500)->toRaw());

        $windowAfter = $sm->availableSendWindow($streamId);
        static::assertSame(500, $windowAfter);
    }

    public function testStreamWindowUpdateIncreasesAvailableWindow(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        /** @var non-negative-int $streamId */
        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);
        $sm->sendData($streamId, str_repeat('x', 65_535));

        $sm->receive(new WindowUpdateFrame(0, 500)->toRaw());

        $windowBefore = $sm->availableSendWindow($streamId);
        static::assertSame(0, $windowBefore);

        $sm->receive(new WindowUpdateFrame($streamId, 500)->toRaw());

        $windowAfter = $sm->availableSendWindow($streamId);
        static::assertSame(500, $windowAfter);
    }

    public function testReceiveDataConsumesFlowControlAndSendsWindowUpdate(): void
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

        $dataRaw = new DataFrame(1, str_repeat('x', 100), false)->toRaw();
        [$responseFrames, $events] = $sm->receive($dataRaw);

        static::assertCount(1, $responseFrames);
        $encoded = $responseFrames[0];
        static::assertIsString($encoded);
        static::assertSame(26, strlen($encoded));

        [$connRaw] = Frame\decode($encoded, 0);
        [$streamRaw] = Frame\decode($encoded, 13);

        $connectionUpdate = WindowUpdateFrame::fromRaw($connRaw);
        $streamUpdate = WindowUpdateFrame::fromRaw($streamRaw);
        static::assertInstanceOf(WindowUpdateFrame::class, $connectionUpdate);
        static::assertInstanceOf(WindowUpdateFrame::class, $streamUpdate);
        static::assertSame(0, $connectionUpdate->streamId);
        static::assertSame(100, $connectionUpdate->windowSizeIncrement);
        static::assertSame(1, $streamUpdate->streamId);
        static::assertSame(100, $streamUpdate->windowSizeIncrement);
    }

    public function testMaxConcurrentStreamsEnforced(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $settingsFrame = new Frame\SettingsFrame([Setting::MaxConcurrentStreams->value => 2], false)->toRaw();
        $sm->receive($settingsFrame);

        $s1 = $sm->nextStreamId();
        $sm->sendHeadersEncoded($s1, [new Header(':method', 'GET')]);
        $s2 = $sm->nextStreamId();
        $sm->sendHeadersEncoded($s2, [new Header(':method', 'GET')]);

        $this->expectException(FlowControlException::class);

        $s3 = $sm->nextStreamId();
        $sm->sendHeadersEncoded($s3, [new Header(':method', 'GET')]);
    }

    public function testReceiveDataOnHalfClosedLocalTransitionsToClosedAfterEndStream(): void
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

        $closedFound = false;
        foreach ($events as $event) {
            if (!$event instanceof StreamClosed) {
                continue;
            }

            $closedFound = true;
        }

        static::assertTrue($closedFound);
    }
}
