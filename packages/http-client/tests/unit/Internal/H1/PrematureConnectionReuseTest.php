<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal\H1;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\H1\Transport;
use Psl\HTTP\Client\Tests\Fixture\H1\FakeStream;
use Psl\HTTP\Message\Request;
use Psl\URL;

final class PrematureConnectionReuseTest extends TestCase
{
    public function testKeepAliveIsTrueWhileBodyIsUnconsumed(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 11\r\n\r\nHello World");
        $connection = new H1Connection($stream);
        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/');

        [$tx, $keepAlive] = Transport::exchange($connection, $request, $url, 8192);

        static::assertTrue($keepAlive, 'Transport says the connection can be reused');
        static::assertNotNull($tx->response->body);

        static::assertFalse(
            $tx->response->body->reachedEndOfDataSource(),
            'Body must be fully consumed before the connection is safe to reuse',
        );
    }

    public function testReuseBeforeBodyConsumptionCorruptsNextExchange(): void
    {
        $data =
            "HTTP/1.1 200 OK\r\ncontent-length: 11\r\n\r\nHello World"
            . "HTTP/1.1 200 OK\r\ncontent-length: 3\r\n\r\nBye";

        $stream = new FakeStream($data);
        $connection = new H1Connection($stream);
        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/');

        [$tx1, $keepAlive] = Transport::exchange($connection, $request, $url, 8192);

        static::assertTrue($keepAlive);

        $this->expectException(ProtocolException::class);
        Transport::exchange($connection, $request, $url, 8192);
    }

    public function testReuseAfterBodyConsumptionSucceeds(): void
    {
        $data =
            "HTTP/1.1 200 OK\r\ncontent-length: 11\r\n\r\nHello World"
            . "HTTP/1.1 200 OK\r\ncontent-length: 3\r\n\r\nBye";

        $stream = new FakeStream($data);
        $connection = new H1Connection($stream);
        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/');

        [$tx1, $keepAlive] = Transport::exchange($connection, $request, $url, 8192);

        static::assertTrue($keepAlive);

        $body1 = $tx1->response->body;
        static::assertNotNull($body1);
        static::assertSame('Hello World', $body1->readAll());

        [$tx2, $keepAlive2] = Transport::exchange($connection, $request, $url, 8192);

        static::assertSame(200, $tx2->response->status);
        $body2 = $tx2->response->body;
        static::assertNotNull($body2);
        static::assertSame('Bye', $body2->readAll());
    }
}
