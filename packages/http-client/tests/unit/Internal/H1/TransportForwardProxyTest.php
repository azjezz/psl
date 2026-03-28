<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal\H1;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\Exception\RequestException;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\H1\Transport;
use Psl\HTTP\Client\Tests\Fixture\H1\FakeStream;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\Network;
use Psl\URL;

use function str_contains;
use function strlen;

final class TransportForwardProxyTest extends TestCase
{
    public function testForwardProxySetsAbsoluteFormRequestTarget(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            isForwardProxy: true,
        );

        $url = URL\parse('http://target.example.com:8080/path?q=1');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/path?q=1', headers: FieldMap::from([[
            'accept',
            '*/*',
        ]]));

        [$transaction, $keepAlive] = Transport::exchange($connection, $request, $url, new ClientConfiguration());

        static::assertSame(200, $transaction->response->status);
        static::assertTrue($keepAlive);

        static::assertTrue(
            str_contains($stream->written, 'GET http://target.example.com:8080/path?q=1 HTTP/1.1'),
            'Request line should use absolute-form target for forward proxy',
        );
    }

    public function testForwardProxyInjectsProxyAuthorizationHeader(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            isForwardProxy: true,
            proxyAuthorization: 'Basic dXNlcjpwYXNz',
        );

        $url = URL\parse('http://target.example.com/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::from([[
            'accept',
            '*/*',
        ]]));

        [$transaction, $_] = Transport::exchange($connection, $request, $url, new ClientConfiguration());

        static::assertSame(200, $transaction->response->status);
        static::assertTrue(
            str_contains($stream->written, 'proxy-authorization: Basic dXNlcjpwYXNz'),
            'Proxy-Authorization header should be injected for forward proxy',
        );
    }

    public function testForwardProxyDoesNotOverrideExistingProxyAuthorizationHeader(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            isForwardProxy: true,
            proxyAuthorization: 'Basic default',
        );

        $url = URL\parse('http://target.example.com/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::from([
            ['accept',              '*/*'],
            ['proxy-authorization', 'Bearer custom-token'],
        ]));

        [$transaction, $_] = Transport::exchange($connection, $request, $url, new ClientConfiguration());

        static::assertSame(200, $transaction->response->status);
        static::assertTrue(
            str_contains($stream->written, 'proxy-authorization: Bearer custom-token'),
            'Existing Proxy-Authorization should be preserved',
        );
        static::assertFalse(
            str_contains($stream->written, 'proxy-authorization: Basic default'),
            'Default proxy authorization should not be added when one already exists',
        );
    }

    public function testForwardProxyWithoutAuthorizationDoesNotAddHeader(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            isForwardProxy: true,
            proxyAuthorization: null,
        );

        $url = URL\parse('http://target.example.com/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::from([[
            'accept',
            '*/*',
        ]]));

        [$transaction, $_] = Transport::exchange($connection, $request, $url, new ClientConfiguration());

        static::assertSame(200, $transaction->response->status);
        static::assertFalse(
            str_contains($stream->written, 'proxy-authorization'),
            'No Proxy-Authorization header should be added when proxyAuthorization is null',
        );
    }

    public function testTrailersWithoutBodyThrowsRequestException(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection($stream, new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()));

        $deferred = new Async\Deferred();
        $deferred->complete(FieldMap::from([['x-checksum', 'abc']]));

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(
            method: 'POST',
            url: $url,
            requestTarget: '/',
            headers: FieldMap::from([['accept', '*/*']]),
            body: null,
            trailers: $deferred->getAwaitable(),
        );

        static::expectException(RequestException::class);
        static::expectExceptionMessage('Trailers cannot be sent without a message body');

        Transport::exchange($connection, $request, $url, new ClientConfiguration());
    }

    public function testEmptyResolvedTrailersAreDiscarded(): void
    {
        $body = 'hello';
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection($stream, new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()));

        $deferred = new Async\Deferred();
        $deferred->complete(FieldMap::default());

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(
            method: 'POST',
            url: $url,
            requestTarget: '/',
            headers: FieldMap::from([
                ['accept', '*/*'],
                ['content-length', (string) strlen($body)],
            ]),
            body: new IO\MemoryHandle($body),
            trailers: $deferred->getAwaitable(),
        );

        [$transaction, $_] = Transport::exchange($connection, $request, $url, new ClientConfiguration());

        static::assertSame(200, $transaction->response->status);
        static::assertTrue(
            str_contains($stream->written, $body),
            'Body should still be sent even when trailers resolve to empty',
        );
    }

    public function testNonForwardProxyConnectionDoesNotModifyRequestTarget(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            isForwardProxy: false,
        );

        $url = URL\parse('http://target.example.com:8080/path');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/path', headers: FieldMap::from([[
            'accept',
            '*/*',
        ]]));

        [$transaction, $_] = Transport::exchange($connection, $request, $url, new ClientConfiguration());

        static::assertSame(200, $transaction->response->status);
        static::assertTrue(
            str_contains($stream->written, 'GET /path HTTP/1.1'),
            'Non-proxy connection should use origin-form request target',
        );
        static::assertFalse(
            str_contains($stream->written, 'GET http://'),
            'Non-proxy connection should not use absolute-form',
        );
    }

    public function testForwardProxyPostWithBody(): void
    {
        $body = 'post-data';
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            isForwardProxy: true,
            proxyAuthorization: 'Bearer token123',
        );

        $url = URL\parse('http://api.example.com/submit');
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

        [$transaction, $_] = Transport::exchange($connection, $request, $url, new ClientConfiguration());

        static::assertSame(200, $transaction->response->status);
        static::assertTrue(
            str_contains($stream->written, 'POST http://api.example.com/submit HTTP/1.1'),
            'POST should use absolute-form target through forward proxy',
        );
        static::assertTrue(
            str_contains($stream->written, 'proxy-authorization: Bearer token123'),
            'Proxy-Authorization should be injected on POST through forward proxy',
        );
        static::assertTrue(str_contains($stream->written, $body), 'Request body should be sent');
    }
}
