<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\Frame;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Internal\StateMachine;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

use function ord;

final class SendResponseHeadersEncodedTest extends TestCase
{
    public function testSendResponseHeadersEncoded(): void
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

        $result = $sm->sendResponseHeadersEncoded(1, '200', [
            new Header('content-type', 'text/plain'),
        ]);

        static::assertNotEmpty($result);
    }

    public function testSendResponseHeadersEncodedWithEndStream(): void
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

        $result = $sm->sendResponseHeadersEncoded(1, '204', [], true);

        static::assertNotEmpty($result);
        $flags = ord($result[4]);
        static::assertSame(0x01, $flags & 0x01);
    }

    public function testSendResponseHeadersEncodedLowercasesHeaders(): void
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

        $result = $sm->sendResponseHeadersEncoded(1, '200', [
            new Header('Content-Type', 'text/html'),
            new Header('X-Custom', 'value'),
        ]);

        static::assertNotEmpty($result);
    }
}
