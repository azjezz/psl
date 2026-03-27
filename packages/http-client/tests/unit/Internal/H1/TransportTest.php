<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal\H1;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Exception\RequestException;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\H1\Transport;
use Psl\HTTP\Client\Internal\PoolReleasingBodyHandle;
use Psl\HTTP\Client\Tests\Fixture\H1\FakeStream;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;
use Psl\URL;

use function chr;
use function dechex;
use function str_repeat;
use function strlen;

final class TransportTest extends TestCase
{
    /**
     * @return array{Transaction, bool}
     */
    private static function exchange(
        string $serverResponse,
        null|Request $request = null,
        int $maxHeaderSize = 8192,
    ): array {
        $url = URL\parse('http://127.0.0.1:8080/');
        $request ??= new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::from([[
            'accept',
            '*/*',
        ]]));

        if ($request->url === null) {
            $request = $request->withUrl($url);
        }

        if ($request->requestTarget === '') {
            $request = $request->withRequestTarget('/');
        }

        $stream = new FakeStream($serverResponse);
        $connection = new H1Connection($stream, new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()));

        return Transport::exchange(
            $connection,
            $request,
            $request->url ?? $url,
            new ClientConfiguration(maxResponseHeaderSize: $maxHeaderSize),
        );
    }

    public function testNormalResponse(): void
    {
        [$transaction, $keepAlive] = self::exchange("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");

        static::assertSame(200, $transaction->response->status);
        static::assertTrue($keepAlive);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('ok', $body->readAll());
    }

    public function testEmptyResponseThrowsProtocol(): void
    {
        $this->expectException(ProtocolException::class);

        self::exchange('');
    }

    public function testGarbageResponseThrowsProtocol(): void
    {
        $this->expectException(ProtocolException::class);

        self::exchange("THIS IS NOT HTTP\r\n\r\n");
    }

    public function testOversizedHeadersThrowsProtocol(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('maximum size');

        $big = 'x-huge: ' . str_repeat('A', 512) . "\r\n";
        self::exchange("HTTP/1.1 200 OK\r\n{$big}\r\n", maxHeaderSize: 128);
    }

    public function testNullBytesInBody(): void
    {
        $body = "before\x00middle\x00after";
        $len = strlen($body);
        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\ncontent-length: {$len}\r\n\r\n{$body}");

        $responseBody = $transaction->response->body;
        static::assertNotNull($responseBody);
        static::assertSame($body, $responseBody->readAll());
    }

    public function testMalformedChunkedEncodingThrows(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Invalid chunk size');

        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\ntransfer-encoding: chunked\r\n\r\nNOTHEX\r\n");

        $transaction->response->body?->readAll();
    }

    public function testConnectionCloseHeader(): void
    {
        [, $keepAlive] = self::exchange("HTTP/1.1 200 OK\r\nconnection: close\r\ncontent-length: 0\r\n\r\n");

        static::assertFalse($keepAlive);
    }

    public function test204HasNoBody(): void
    {
        [$transaction, $_] = self::exchange("HTTP/1.1 204 No Content\r\n\r\n");

        self::assertSame(204, $transaction->response->status);
        self::assertNull($transaction->response->body);
    }

    public function testPostWithBody(): void
    {
        $body = 'hello server';
        $url = URL\parse('http://127.0.0.1:8080/submit');
        $request = new Request(
            method: 'POST',
            url: $url,
            requestTarget: '/submit',
            headers: FieldMap::from([
                ['content-type', 'text/plain'],
                ['content-length', (string) strlen($body)],
            ]),
            body: new IO\MemoryHandle($body),
        );

        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\ncontent-length: 12\r\n\r\nhello server", $request);

        $responseBody = $transaction->response->body;
        static::assertNotNull($responseBody);
        static::assertSame('hello server', $responseBody->readAll());
    }

    public function testCollectsInformationalResponses(): void
    {
        $raw = "HTTP/1.1 100 Continue\r\n\r\nHTTP/1.1 200 OK\r\ncontent-length: 4\r\n\r\ndone";

        [$transaction, $_] = self::exchange($raw);

        static::assertSame(200, $transaction->response->status);
        static::assertCount(1, $transaction->informational);
        static::assertSame(100, $transaction->informational[0]->status);
        $responseBody = $transaction->response->body;
        static::assertNotNull($responseBody);
        static::assertSame('done', $responseBody->readAll());
    }

    public function testHttp10NoKeepAlive(): void
    {
        [, $keepAlive] = self::exchange("HTTP/1.0 200 OK\r\ncontent-length: 0\r\n\r\n");

        static::assertFalse($keepAlive);
    }

    public function testHttp10WithKeepAliveHeader(): void
    {
        [, $keepAlive] = self::exchange("HTTP/1.0 200 OK\r\nconnection: keep-alive\r\ncontent-length: 0\r\n\r\n");

        static::assertTrue($keepAlive);
    }

    public function testEmptyHeaderNameThrows(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Empty header name');

        self::exchange("HTTP/1.1 200 OK\r\n: value\r\ncontent-length: 0\r\n\r\n");
    }

    public function testHeaderWithoutColonThrows(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Invalid header line');

        self::exchange("HTTP/1.1 200 OK\r\nbrokenheader\r\n\r\n");
    }

    public function testUntilCloseBody(): void
    {
        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\n\r\nstreaming data");

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('streaming data', $body->readAll());
    }

    public function testChunkedResponseWithExtension(): void
    {
        $raw = "HTTP/1.1 200 OK\r\ntransfer-encoding: chunked\r\n\r\n5;ext=val\r\nhello\r\n0\r\n\r\n";
        [$transaction, $_] = self::exchange($raw);

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('hello', $body->readAll());
    }

    public function testMultipleSetCookieHeaders(): void
    {
        $raw = "HTTP/1.1 200 OK\r\nset-cookie: a=1\r\nset-cookie: b=2\r\ncontent-length: 0\r\n\r\n";
        [$transaction, $_] = self::exchange($raw);

        $cookies = $transaction->response->headers->getAll('set-cookie');
        static::assertCount(2, $cookies);
    }

    public function testBinaryBodyData(): void
    {
        $binary = '';
        for ($i = 0; $i < 256; $i++) {
            $binary .= chr($i);
        }

        $len = strlen($binary);
        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\ncontent-length: {$len}\r\n\r\n{$binary}");

        $responseBody = $transaction->response->body;
        static::assertNotNull($responseBody);
        static::assertSame($binary, $responseBody->readAll());
    }

    public function testUnsupportedHttpVersionThrows(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Unsupported HTTP version');

        self::exchange("HTTP/2.0 200 OK\r\n\r\n");
    }

    public function testNonNumericStatusCodeThrows(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Invalid status code');

        self::exchange("HTTP/1.1 abc OK\r\n\r\n");
    }

    public function testLargeChunkedResponse(): void
    {
        $chunk = str_repeat('X', 4096);
        $hex = dechex(4096);
        $raw =
            "HTTP/1.1 200 OK\r\ntransfer-encoding: chunked\r\n\r\n"
            . "{$hex}\r\n{$chunk}\r\n"
            . "{$hex}\r\n{$chunk}\r\n"
            . "0\r\n\r\n";

        [$transaction, $_] = self::exchange($raw);

        $body = $transaction->response->body;
        static::assertNotNull($body);
        $data = $body->readAll();
        static::assertSame(8192, strlen($data));
    }

    public function testHeadRequestNoBody(): void
    {
        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'HEAD', url: $url, requestTarget: '/', headers: FieldMap::default());

        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\ncontent-length: 5000\r\n\r\n", $request);

        static::assertSame(200, $transaction->response->status);
        static::assertNull($transaction->response->body);
    }

    public function test304HasNoBody(): void
    {
        [$transaction, $_] = self::exchange("HTTP/1.1 304 Not Modified\r\n\r\n");

        self::assertNull($transaction->response->body);
    }

    public function testExpectContinueWithServerSending100(): void
    {
        $body = 'post data';
        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(
            method: 'POST',
            url: $url,
            requestTarget: '/',
            headers: FieldMap::from([
                ['expect', '100-continue'],
                ['content-length', (string) strlen($body)],
            ]),
            body: new IO\MemoryHandle($body),
        );

        [$transaction, $_] = self::exchange(
            "HTTP/1.1 100 Continue\r\n\r\nHTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok",
            $request,
        );

        static::assertSame(200, $transaction->response->status);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('ok', $body->readAll());
    }

    public function testExpectContinueWithServerRejectingDirectly(): void
    {
        $body = 'post data';
        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(
            method: 'POST',
            url: $url,
            requestTarget: '/',
            headers: FieldMap::from([
                ['expect', '100-continue'],
                ['content-length', (string) strlen($body)],
            ]),
            body: new IO\MemoryHandle($body),
        );

        [$transaction, $_] = self::exchange(
            "HTTP/1.1 417 Expectation Failed\r\ncontent-length: 6\r\n\r\nfailed",
            $request,
        );

        static::assertSame(417, $transaction->response->status);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('failed', $body->readAll());
    }

    public function testExpectContinueWithServer200WithoutBody(): void
    {
        $body = 'post data';
        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(
            method: 'POST',
            url: $url,
            requestTarget: '/',
            headers: FieldMap::from([
                ['expect', '100-continue'],
                ['content-length', (string) strlen($body)],
            ]),
            body: new IO\MemoryHandle($body),
        );

        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\ncontent-length: 11\r\n\r\nno body thx", $request);

        static::assertSame(200, $transaction->response->status);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('no body thx', $body->readAll());
    }

    public function testChunkedResponseWithTrailers(): void
    {
        $raw =
            "HTTP/1.1 200 OK\r\ntransfer-encoding: chunked\r\n\r\n"
            . "5\r\nhello\r\n0\r\nx-checksum: abc123\r\nx-count: 42\r\n\r\n";
        [$transaction, $_] = self::exchange($raw);

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('hello', $body->readAll());

        $trailers = $transaction->response->trailers;
        static::assertNotNull($trailers);
        $resolved = $trailers->await();
        static::assertSame('abc123', $resolved->get('x-checksum'));
        static::assertSame('42', $resolved->get('x-count'));
    }

    public function testChunkedResponseWithoutTrailers(): void
    {
        $raw = "HTTP/1.1 200 OK\r\ntransfer-encoding: chunked\r\n\r\n5\r\nhello\r\n0\r\n\r\n";
        [$transaction, $_] = self::exchange($raw);

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertSame('hello', $body->readAll());

        $trailers = $transaction->response->trailers;
        static::assertNotNull($trailers);
        $resolved = $trailers->await();
        static::assertTrue($resolved->isEmpty());
    }

    public function testNonChunkedResponseHasNoTrailers(): void
    {
        [$transaction, $_] = self::exchange("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");

        static::assertNull($transaction->response->trailers);
    }

    public function testExchangeWithoutUrlThrowsRequestException(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection($stream, new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()));

        $request = new Request(method: 'GET', url: null, requestTarget: '/');

        $this->expectException(RequestException::class);
        $connection->exchange($request, new ClientConfiguration());
    }

    public function testKeepAliveConnectionWrapsBodyWithPoolReleasingHandle(): void
    {
        $released = false;
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 5\r\n\r\nhello");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            static function (Network\StreamInterface $s) use (&$released): void {
                $released = true;
            },
        );

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::from([
            ['accept', '*/*'],
        ]));

        $transaction = $connection->exchange($request, new ClientConfiguration());

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertNotInstanceOf(PoolReleasingBodyHandle::class, $body);

        $transaction = $connection->finalize($transaction);
        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertInstanceOf(PoolReleasingBodyHandle::class, $body);

        static::assertSame('hello', $body->readAll());
        static::assertTrue($released);
    }
}
