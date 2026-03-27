<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal\H1;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\PoolReleasingBodyHandle;
use Psl\HTTP\Client\Tests\Fixture\H1\FakeStream;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\Network;
use Psl\URL;

final class H1ConnectionTest extends TestCase
{
    public function testFinalizeCallsOnReleaseWhenKeepAliveAndBodyIsNull(): void
    {
        $releasedStream = null;
        $releasedMetadata = null;
        $metadata = new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp());
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 0\r\n\r\n");
        $connection = new H1Connection($stream, $metadata, static function (
            Network\StreamInterface $s,
            ConnectionMetadata $m,
        ) use (&$releasedStream, &$releasedMetadata): void {
            $releasedStream = $s;
            $releasedMetadata = $m;
        });

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::default());
        $transaction = $connection->exchange($request, new ClientConfiguration());

        static::assertNull($transaction->response->body);

        $finalized = $connection->finalize($transaction);

        static::assertSame($transaction, $finalized);
        static::assertSame($stream, $releasedStream);
        static::assertSame($metadata, $releasedMetadata);
    }

    public function testFinalizeCallsOnReleaseWhenKeepAliveAndBodyFullyConsumed(): void
    {
        $released = false;
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 5\r\n\r\nhello");
        $metadata = new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp());
        $connection = new H1Connection($stream, $metadata, static function () use (&$released): void {
            $released = true;
        });

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::default());
        $transaction = $connection->exchange($request, new ClientConfiguration());

        $body = $transaction->response->body;
        static::assertNotNull($body);
        $body->readAll();
        static::assertTrue($body->reachedEndOfDataSource());

        $finalized = $connection->finalize($transaction);

        static::assertSame($transaction, $finalized);
        static::assertTrue($released);
    }

    public function testFinalizeReturnsTransactionAsIsWhenOnReleaseIsNull(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 5\r\n\r\nhello");
        $connection = new H1Connection($stream, new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()));

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::default());
        $transaction = $connection->exchange($request, new ClientConfiguration());

        $finalized = $connection->finalize($transaction);

        static::assertSame($transaction, $finalized);
    }

    public function testFinalizeReturnsTransactionAsIsWhenNotKeepAlive(): void
    {
        $released = false;
        $stream = new FakeStream("HTTP/1.1 200 OK\r\nconnection: close\r\ncontent-length: 5\r\n\r\nhello");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            static function () use (&$released): void {
                $released = true;
            },
        );

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::default());
        $transaction = $connection->exchange($request, new ClientConfiguration());

        static::assertFalse($connection->keepAlive);

        $finalized = $connection->finalize($transaction);

        static::assertSame($transaction, $finalized);
        static::assertFalse($released);
    }

    public function testFinalizeWrapsUnconsumedBodyWithPoolReleasingHandle(): void
    {
        $released = false;
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 5\r\n\r\nhello");
        $metadata = new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp());
        $connection = new H1Connection($stream, $metadata, static function () use (&$released): void {
            $released = true;
        });

        $url = URL\parse('http://127.0.0.1:8080/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::default());
        $transaction = $connection->exchange($request, new ClientConfiguration());

        $body = $transaction->response->body;
        static::assertNotNull($body);
        static::assertFalse($body->reachedEndOfDataSource());

        $finalized = $connection->finalize($transaction);

        static::assertNotSame($transaction, $finalized);
        $wrappedBody = $finalized->response->body;
        static::assertNotNull($wrappedBody);
        static::assertInstanceOf(PoolReleasingBodyHandle::class, $wrappedBody);

        static::assertFalse($released);
        static::assertSame('hello', $wrappedBody->readAll());
        static::assertTrue($released);
    }

    public function testForwardProxyUsesAbsoluteFormRequestTarget(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            null,
            isForwardProxy: true,
        );

        $url = URL\parse('http://example.com/path?q=1');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/path?q=1', headers: FieldMap::default());
        $connection->exchange($request, new ClientConfiguration());

        static::assertStringContainsString('http://example.com/path?q=1', $stream->written);
    }

    public function testForwardProxyAddsProxyAuthorizationHeader(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            null,
            isForwardProxy: true,
            proxyAuthorization: 'Basic dXNlcjpwYXNz',
        );

        $url = URL\parse('http://example.com/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::default());
        $connection->exchange($request, new ClientConfiguration());

        static::assertStringContainsString('proxy-authorization: Basic dXNlcjpwYXNz', $stream->written);
    }

    public function testForwardProxyDoesNotOverrideExistingProxyAuthorizationHeader(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection(
            $stream,
            new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()),
            null,
            isForwardProxy: true,
            proxyAuthorization: 'Basic c2VydmVyOnBhc3M=',
        );

        $url = URL\parse('http://example.com/');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/', headers: FieldMap::from([[
            'proxy-authorization',
            'Basic Y2xpZW50OnBhc3M=',
        ]]));
        $connection->exchange($request, new ClientConfiguration());

        static::assertStringContainsString('proxy-authorization: Basic Y2xpZW50OnBhc3M=', $stream->written);
        static::assertStringNotContainsString('Basic c2VydmVyOnBhc3M=', $stream->written);
    }

    public function testNonForwardProxyUsesRelativeRequestTarget(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
        $connection = new H1Connection($stream, new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()));

        $url = URL\parse('http://example.com/path');
        $request = new Request(method: 'GET', url: $url, requestTarget: '/path', headers: FieldMap::default());
        $connection->exchange($request, new ClientConfiguration());

        static::assertStringContainsString('GET /path HTTP/1.1', $stream->written);
        static::assertStringNotContainsString('http://example.com', $stream->written);
    }

    public function testForwardProxyFieldsDefaultToDisabled(): void
    {
        $stream = new FakeStream("HTTP/1.1 200 OK\r\ncontent-length: 0\r\n\r\n");
        $connection = new H1Connection($stream, new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp()));

        static::assertFalse($connection->isForwardProxy);
        static::assertNull($connection->proxyAuthorization);
    }
}
