<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\Exception\StreamException;
use Psl\H2\Frame;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Internal\StateMachine;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

final class SendDataEncodedTest extends TestCase
{
    public function testSendDataEncodedReturnsWireBytes(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $result = $sm->sendDataEncoded($streamId, 'hello');

        static::assertNotEmpty($result);
        static::assertSame(9 + 5, strlen($result));
    }

    public function testSendDataEncodedWithEndStream(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $result = $sm->sendDataEncoded($streamId, 'end', true);

        $flags = ord($result[4]);
        static::assertSame(0x01, $flags & 0x01);
    }

    public function testSendDataEncodedOnIdleStreamThrows(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $this->expectException(StreamException::class);

        $sm->sendDataEncoded(99, 'data', false);
    }

    public function testSendDataEncodedEndStreamOnHalfClosedRemoteClosesStream(): void
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

        $sm->sendDataEncoded(1, 'response', true);

        $this->expectException(StreamException::class);

        $sm->sendDataEncoded(1, 'after-close');
    }

    public function testSendDataEncodedEndStreamOnOpenTransitionsToHalfClosedLocal(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $sm->sendDataEncoded($streamId, 'body', true);

        $this->expectException(StreamException::class);

        $sm->sendDataEncoded($streamId, 'more');
    }

    public function testSendDataEncodedEmptyPayload(): void
    {
        $sm = new StateMachine(true);
        $sm->initialize();

        $streamId = $sm->nextStreamId();
        $sm->sendHeadersEncoded($streamId, [new Header(':method', 'POST')]);

        $result = $sm->sendDataEncoded($streamId, '', true);

        static::assertSame(9, strlen($result));
    }
}
