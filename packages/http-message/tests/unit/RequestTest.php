<?php

declare(strict_types=1);

namespace Psl\HTTP\Message\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\HTTP\Message;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\URL;

final class RequestTest extends TestCase
{
    public function testConstructionWithUrl(): void
    {
        $url = URL\parse('https://example.com/api/users?page=2');
        $request = new Request(Message\METHOD_GET, $url);

        static::assertSame(Message\METHOD_GET, $request->method);
        static::assertSame($url, $request->url);
        static::assertSame('/api/users?page=2', $request->requestTarget);
        static::assertSame(ProtocolVersion::V11, $request->protocolVersion);
        static::assertTrue($request->headers->isEmpty());
        static::assertNull($request->body);
        static::assertNull($request->trailers);
    }

    public function testRequestTargetDerivedFromUrlPath(): void
    {
        $url = URL\parse('https://example.com/foo');
        $request = new Request(Message\METHOD_GET, $url);

        static::assertSame('/foo', $request->requestTarget);
    }

    public function testRequestTargetDefaultsToSlashForEmptyPath(): void
    {
        $url = URL\parse('https://example.com');
        $request = new Request(Message\METHOD_GET, $url);

        static::assertSame('/', $request->requestTarget);
    }

    public function testRequestTargetDefaultsToSlashWhenNoUrl(): void
    {
        $request = new Request(Message\METHOD_GET, null);

        static::assertSame('/', $request->requestTarget);
    }

    public function testExplicitRequestTarget(): void
    {
        $request = new Request(Message\METHOD_CONNECT, null, 'example.com:443');

        static::assertSame(Message\METHOD_CONNECT, $request->method);
        static::assertNull($request->url);
        static::assertSame('example.com:443', $request->requestTarget);
    }

    public function testOptionsAsterisk(): void
    {
        $request = new Request(Message\METHOD_OPTIONS, null, '*');

        static::assertSame('*', $request->requestTarget);
    }

    public function testWithMethod(): void
    {
        $request = new Request(Message\METHOD_GET, null);
        $modified = $request->withMethod(Message\METHOD_POST);

        static::assertSame(Message\METHOD_GET, $request->method);
        static::assertSame(Message\METHOD_POST, $modified->method);
        static::assertNotSame($request, $modified);
    }

    public function testWithUrl(): void
    {
        $url = URL\parse('https://example.com/foo');
        $request = new Request(Message\METHOD_GET, null);
        $modified = $request->withUrl($url);

        static::assertNull($request->url);
        static::assertSame($url, $modified->url);
    }

    public function testWithRequestTarget(): void
    {
        $request = new Request(Message\METHOD_GET, null);
        $modified = $request->withRequestTarget('/bar');

        static::assertSame('/', $request->requestTarget);
        static::assertSame('/bar', $modified->requestTarget);
    }

    public function testWithProtocolVersion(): void
    {
        $request = new Request(Message\METHOD_GET, null);
        $modified = $request->withProtocolVersion(ProtocolVersion::V20);

        static::assertSame(ProtocolVersion::V11, $request->protocolVersion);
        static::assertSame(ProtocolVersion::V20, $modified->protocolVersion);
    }

    public function testWithHeaders(): void
    {
        $request = new Request(Message\METHOD_GET, null, headers: FieldMap::from([['host', 'example.com']]));
        $modified = $request->withHeaders(FieldMap::from([['content-type', 'text/plain']]));

        static::assertSame([['host', 'example.com']], $request->headers->toArray());
        static::assertSame([['content-type', 'text/plain']], $modified->headers->toArray());
    }

    public function testWithHeaderReplacesExisting(): void
    {
        $request = new Request(Message\METHOD_GET, null, headers: FieldMap::from([
            ['content-type', 'text/html'],
            ['accept',       'application/json'],
        ]));
        $modified = $request->withHeader('content-type', 'text/plain');

        static::assertSame(
            [
                ['content-type', 'text/plain'],
                ['accept',       'application/json'],
            ],
            $modified->headers->toArray(),
        );
    }

    public function testWithHeaderCaseInsensitive(): void
    {
        $request = new Request(Message\METHOD_GET, null, headers: FieldMap::from([['Content-Type', 'text/html']]));
        $modified = $request->withHeader('content-type', 'text/plain');

        static::assertSame([['content-type', 'text/plain']], $modified->headers->toArray());
    }

    public function testWithHeaderAppendsWhenNotFound(): void
    {
        $request = new Request(Message\METHOD_GET, null);
        $modified = $request->withHeader('accept', 'text/html');

        static::assertSame([['accept', 'text/html']], $modified->headers->toArray());
    }

    public function testWithAddedHeader(): void
    {
        $request = new Request(Message\METHOD_GET, null, headers: FieldMap::from([['accept', 'text/html']]));
        $modified = $request->withAddedHeader('accept', 'application/json');

        static::assertSame(
            [
                ['accept', 'text/html'],
                ['accept', 'application/json'],
            ],
            $modified->headers->toArray(),
        );
    }

    public function testWithoutHeader(): void
    {
        $request = new Request(Message\METHOD_GET, null, headers: FieldMap::from([
            ['content-type', 'text/html'],
            ['accept',       'text/html'],
        ]));
        $modified = $request->withoutHeader('content-type');

        static::assertSame([['accept', 'text/html']], $modified->headers->toArray());
    }

    public function testWithoutHeaderCaseInsensitive(): void
    {
        $request = new Request(Message\METHOD_GET, null, headers: FieldMap::from([['Content-Type', 'text/html']]));
        $modified = $request->withoutHeader('content-type');

        static::assertTrue($modified->headers->isEmpty());
    }

    public function testWithBody(): void
    {
        $body = new IO\MemoryHandle('hello');
        $request = new Request(Message\METHOD_POST, null);
        $modified = $request->withBody($body);

        static::assertNull($request->body);
        static::assertSame($body, $modified->body);
    }

    public function testWithBodyNull(): void
    {
        $body = new IO\MemoryHandle('hello');
        $request = new Request(Message\METHOD_POST, null, body: $body);
        $modified = $request->withBody(null);

        static::assertSame($body, $request->body);
        static::assertNull($modified->body);
    }

    public function testWithTrailers(): void
    {
        $request = new Request(Message\METHOD_POST, null);
        $trailers = Async\Awaitable::<FieldMap>::complete(FieldMap::from([['checksum', 'abc123']]));
        $modified = $request->withTrailers($trailers);

        static::assertNull($request->trailers);
        static::assertSame([['checksum', 'abc123']], $modified->trailers->await()->toArray());
    }

    public function testConstructionWithAllParameters(): void
    {
        $url = URL\parse('https://example.com:8080/api?v=1');
        $body = new IO\MemoryHandle('{"key":"value"}');
        $trailers = Async\Awaitable::<FieldMap>::complete(FieldMap::from([['checksum', 'sha256=abc']]));
        $request = new Request(
            Message\METHOD_POST,
            $url,
            '/api?v=1',
            ProtocolVersion::V20,
            FieldMap::from([['content-type', 'application/json']]),
            $body,
            $trailers,
        );

        static::assertSame(Message\METHOD_POST, $request->method);
        static::assertSame($url, $request->url);
        static::assertSame('/api?v=1', $request->requestTarget);
        static::assertSame(ProtocolVersion::V20, $request->protocolVersion);
        static::assertSame([['content-type', 'application/json']], $request->headers->toArray());
        static::assertSame($body, $request->body);
        static::assertSame([['checksum', 'sha256=abc']], $request->trailers->await()->toArray());
    }

    public function testHeadRequest(): void
    {
        $url = URL\parse('https://example.com/resource');
        $request = new Request(Message\METHOD_HEAD, $url);

        static::assertSame(Message\METHOD_HEAD, $request->method);
        static::assertNull($request->body);
    }

    public function testPutRequestWithBody(): void
    {
        $body = new IO\MemoryHandle('{"name":"updated"}');
        $url = URL\parse('https://example.com/resource/1');
        $request = new Request(Message\METHOD_PUT, $url, body: $body);

        static::assertSame(Message\METHOD_PUT, $request->method);
        static::assertSame('/resource/1', $request->requestTarget);
        static::assertSame($body, $request->body);
    }

    public function testDeleteRequest(): void
    {
        $url = URL\parse('https://example.com/resource/1');
        $request = new Request(Message\METHOD_DELETE, $url);

        static::assertSame(Message\METHOD_DELETE, $request->method);
        static::assertNull($request->body);
    }

    public function testTraceRequest(): void
    {
        $url = URL\parse('https://example.com/');
        $request = new Request(Message\METHOD_TRACE, $url);

        static::assertSame(Message\METHOD_TRACE, $request->method);
    }

    public function testPatchRequestWithBody(): void
    {
        $body = new IO\MemoryHandle('{"op":"replace","path":"/name","value":"new"}');
        $url = URL\parse('https://example.com/resource/1');
        $request = new Request(
            Message\METHOD_PATCH,
            $url,
            headers: FieldMap::from([['content-type', 'application/json-patch+json']]),
            body: $body,
        );

        static::assertSame(Message\METHOD_PATCH, $request->method);
        static::assertSame($body, $request->body);
    }

    public function testWebDavCopyRequest(): void
    {
        $url = URL\parse('https://dav.example.com/file.txt');
        $request = new Request(Message\METHOD_COPY, $url, headers: FieldMap::from([[
            'destination',
            'https://dav.example.com/copy.txt',
        ]]));

        static::assertSame(Message\METHOD_COPY, $request->method);
    }

    public function testWebDavMoveRequest(): void
    {
        $url = URL\parse('https://dav.example.com/old.txt');
        $request = new Request(Message\METHOD_MOVE, $url, headers: FieldMap::from([[
            'destination',
            'https://dav.example.com/new.txt',
        ]]));

        static::assertSame(Message\METHOD_MOVE, $request->method);
    }

    public function testWebDavLockUnlockRequests(): void
    {
        $url = URL\parse('https://dav.example.com/file.txt');
        $lock = new Request(Message\METHOD_LOCK, $url);
        $unlock = new Request(Message\METHOD_UNLOCK, $url, headers: FieldMap::from([[
            'lock-token',
            '<opaquelocktoken:abc>',
        ]]));

        static::assertSame(Message\METHOD_LOCK, $lock->method);
        static::assertSame(Message\METHOD_UNLOCK, $unlock->method);
    }

    public function testReportRequest(): void
    {
        $body = new IO\MemoryHandle('<D:version-tree/>');
        $url = URL\parse('https://dav.example.com/repo');
        $request = new Request(Message\METHOD_REPORT, $url, body: $body);

        static::assertSame(Message\METHOD_REPORT, $request->method);
    }

    public function testMergeRequest(): void
    {
        $body = new IO\MemoryHandle('<D:merge-source/>');
        $url = URL\parse('https://dav.example.com/target');
        $request = new Request(Message\METHOD_MERGE, $url, body: $body);

        static::assertSame(Message\METHOD_MERGE, $request->method);
    }

    public function testSearchRequest(): void
    {
        $body = new IO\MemoryHandle('{"query":"test"}');
        $url = URL\parse('https://example.com/search');
        $request = new Request(
            Message\METHOD_SEARCH,
            $url,
            headers: FieldMap::from([['content-type', 'application/json']]),
            body: $body,
        );

        static::assertSame(Message\METHOD_SEARCH, $request->method);
    }

    public function testQueryRequest(): void
    {
        $body = new IO\MemoryHandle('SELECT * FROM items');
        $url = URL\parse('https://example.com/query');
        $request = new Request(Message\METHOD_QUERY, $url, body: $body);

        static::assertSame(Message\METHOD_QUERY, $request->method);
    }

    public function testPurgeRequest(): void
    {
        $url = URL\parse('https://cdn.example.com/assets/style.css');
        $request = new Request(Message\METHOD_PURGE, $url);

        static::assertSame(Message\METHOD_PURGE, $request->method);
    }

    public function testWithMethodPreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com/resource');
        $body = new IO\MemoryHandle('data');
        $request = new Request(
            Message\METHOD_POST,
            $url,
            headers: FieldMap::from([['content-type', 'text/plain']]),
            body: $body,
        );
        $modified = $request->withMethod(Message\METHOD_PUT);

        static::assertSame(Message\METHOD_PUT, $modified->method);
        static::assertSame($url, $modified->url);
        static::assertSame($body, $modified->body);
        static::assertSame($request->headers->toArray(), $modified->headers->toArray());
    }

    public function testWithHeaderRemovesDuplicates(): void
    {
        $request = new Request(Message\METHOD_GET, null, headers: FieldMap::from([
            ['accept', 'text/html'],
            ['accept', 'application/json'],
            ['host',   'example.com'],
        ]));
        $modified = $request->withHeader('accept', 'text/plain');

        static::assertSame(
            [
                ['accept', 'text/plain'],
                ['host',   'example.com'],
            ],
            $modified->headers->toArray(),
        );
    }
}
