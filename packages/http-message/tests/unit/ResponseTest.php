<?php

declare(strict_types=1);

namespace Psl\HTTP\Message\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\HTTP\Message;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Response;
use Psl\IO;

final class ResponseTest extends TestCase
{
    public function testConstruction(): void
    {
        $response = new Response(Message\STATUS_OK);

        static::assertSame(Message\STATUS_OK, $response->status);
        static::assertSame(ProtocolVersion::V11, $response->protocolVersion);
        static::assertTrue($response->headers->isEmpty());
        static::assertNull($response->body);
        static::assertNull($response->trailers);
    }

    public function testConstructionWithAllParameters(): void
    {
        $body = new IO\MemoryHandle('hello world');
        $trailers = Async\Awaitable::complete(FieldMap::from([['checksum', 'abc']]));
        $response = new Response(
            Message\STATUS_CREATED,
            ProtocolVersion::V20,
            FieldMap::from([['content-type', 'text/plain']]),
            $body,
            $trailers,
        );

        static::assertSame(Message\STATUS_CREATED, $response->status);
        static::assertSame(ProtocolVersion::V20, $response->protocolVersion);
        static::assertSame([['content-type', 'text/plain']], $response->headers->toArray());
        static::assertSame($body, $response->body);
        static::assertSame([['checksum', 'abc']], $response->trailers?->await()->toArray());
    }

    public function testWithStatus(): void
    {
        $response = new Response(Message\STATUS_OK);
        $modified = $response->withStatus(Message\STATUS_NOT_FOUND);

        static::assertSame(Message\STATUS_OK, $response->status);
        static::assertSame(Message\STATUS_NOT_FOUND, $modified->status);
        static::assertNotSame($response, $modified);
    }

    public function testWithProtocolVersion(): void
    {
        $response = new Response(Message\STATUS_OK);
        $modified = $response->withProtocolVersion(ProtocolVersion::V10);

        static::assertSame(ProtocolVersion::V11, $response->protocolVersion);
        static::assertSame(ProtocolVersion::V10, $modified->protocolVersion);
    }

    public function testWithHeaders(): void
    {
        $response = new Response(200, headers: FieldMap::from([['server', 'stack']]));
        $modified = $response->withHeaders(FieldMap::from([['content-type', 'text/html']]));

        static::assertSame([['server', 'stack']], $response->headers->toArray());
        static::assertSame([['content-type', 'text/html']], $modified->headers->toArray());
    }

    public function testWithHeaderReplacesExisting(): void
    {
        $response = new Response(200, headers: FieldMap::from([
            ['content-type', 'text/html'],
            ['server',       'stack'],
        ]));
        $modified = $response->withHeader('content-type', 'application/json');

        static::assertSame(
            [
                ['content-type', 'application/json'],
                ['server',       'stack'],
            ],
            $modified->headers->toArray(),
        );
    }

    public function testWithHeaderCaseInsensitive(): void
    {
        $response = new Response(200, headers: FieldMap::from([['Content-Type', 'text/html']]));
        $modified = $response->withHeader('content-type', 'text/plain');

        static::assertSame([['content-type', 'text/plain']], $modified->headers->toArray());
    }

    public function testWithAddedHeader(): void
    {
        $response = new Response(200, headers: FieldMap::from([['set-cookie', 'a=1']]));
        $modified = $response->withAddedHeader('set-cookie', 'b=2');

        static::assertSame(
            [
                ['set-cookie', 'a=1'],
                ['set-cookie', 'b=2'],
            ],
            $modified->headers->toArray(),
        );
    }

    public function testWithoutHeader(): void
    {
        $response = new Response(200, headers: FieldMap::from([
            ['content-type', 'text/html'],
            ['server',       'stack'],
        ]));
        $modified = $response->withoutHeader('server');

        static::assertSame([['content-type', 'text/html']], $modified->headers->toArray());
    }

    public function testWithBody(): void
    {
        $body = new IO\MemoryHandle('hello');
        $response = new Response(Message\STATUS_OK);
        $modified = $response->withBody($body);

        static::assertNull($response->body);
        static::assertSame($body, $modified->body);
    }

    public function testWithBodyNull(): void
    {
        $body = new IO\MemoryHandle('hello');
        $response = new Response(200, body: $body);
        $modified = $response->withBody(null);

        static::assertSame($body, $response->body);
        static::assertNull($modified->body);
    }

    public function testWithTrailers(): void
    {
        $response = new Response(Message\STATUS_OK);
        $trailers = Async\Awaitable::complete(FieldMap::from([['grpc-status', '0']]));
        $modified = $response->withTrailers($trailers);

        static::assertNull($response->trailers);
        static::assertSame([['grpc-status', '0']], $modified->trailers?->await()->toArray());
    }

    public function testWithHeaderRemovesDuplicates(): void
    {
        $response = new Response(200, headers: FieldMap::from([
            ['set-cookie',   'a=1'],
            ['set-cookie',   'b=2'],
            ['content-type', 'text/html'],
        ]));
        $modified = $response->withHeader('set-cookie', 'c=3');

        static::assertSame(
            [
                ['set-cookie',   'c=3'],
                ['content-type', 'text/html'],
            ],
            $modified->headers->toArray(),
        );
    }
}
