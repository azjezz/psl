<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal\H1;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\Internal\H1\RequestWriter;
use Psl\HTTP\Client\Tests\Fixture\H1\FakeStream;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\URL;

use function strlen;
use function substr_count;

final class RequestWriterTest extends TestCase
{
    private static function write(Request $request, null|URL\URL $url = null, null|FieldMap $trailers = null): string
    {
        $url ??= $request->url ?? URL\parse('http://example.com/');
        $stream = new FakeStream('');
        RequestWriter::write($stream, $request, $url, $trailers);

        return $stream->written;
    }

    public function testBasicGetRequest(): void
    {
        $request = new Request(
            method: 'GET',
            url: URL\parse('http://example.com/path'),
            headers: FieldMap::from([['accept', '*/*']]),
        );

        $request = $request->withRequestTarget('/path');
        $written = self::write($request);

        static::assertStringStartsWith("GET /path HTTP/1.1\r\n", $written);
        static::assertStringContainsString("host: example.com\r\n", $written);
        static::assertStringContainsString("accept: */*\r\n", $written);
        static::assertStringEndsWith("\r\n\r\n", $written);
    }

    public function testHostHeaderNotDuplicatedWhenPresent(): void
    {
        $request = new Request(
            method: 'GET',
            url: URL\parse('http://example.com/'),
            headers: FieldMap::from([['host', 'custom.com']]),
        );

        $request = $request->withRequestTarget('/');
        $written = self::write($request);

        static::assertStringContainsString("host: custom.com\r\n", $written);
        static::assertSame(1, substr_count($written, 'host:'));
    }

    public function testHostIncludesPortWhenNonDefault(): void
    {
        $url = URL\parse('http://example.com:9090/');
        $request = new Request(method: 'GET', url: $url, headers: FieldMap::default());
        $request = $request->withRequestTarget('/');

        $written = self::write($request, $url);

        static::assertStringContainsString("host: example.com:9090\r\n", $written);
    }

    public function testPostWithContentLength(): void
    {
        $body = 'hello world';
        $request = new Request(
            method: 'POST',
            url: URL\parse('http://example.com/submit'),
            headers: FieldMap::from([
                ['content-length', (string) strlen($body)],
                ['content-type', 'text/plain'],
            ]),
            body: new IO\MemoryHandle($body),
        );

        $request = $request->withRequestTarget('/submit');
        $written = self::write($request);

        static::assertStringContainsString("content-length: 11\r\n", $written);
        static::assertStringEndsWith("\r\n\r\nhello world", $written);
        static::assertStringNotContainsString('transfer-encoding', $written);
    }

    public function testPostWithoutContentLengthUsesChunked(): void
    {
        $body = 'test data';
        $request = new Request(
            method: 'POST',
            url: URL\parse('http://example.com/'),
            headers: FieldMap::default(),
            body: new IO\MemoryHandle($body),
        );

        $request = $request->withRequestTarget('/');
        $written = self::write($request);

        static::assertStringContainsString("transfer-encoding: chunked\r\n", $written);
        static::assertStringContainsString("9\r\ntest data\r\n", $written);
        static::assertStringEndsWith("0\r\n\r\n", $written);
    }

    public function testGetWithNoBody(): void
    {
        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'), headers: FieldMap::default());

        $request = $request->withRequestTarget('/');
        $written = self::write($request);

        static::assertStringEndsWith("\r\n\r\n", $written);
        static::assertStringNotContainsString('transfer-encoding', $written);
    }

    public function testHeadersWithCrlfAreStripped(): void
    {
        $request = new Request(
            method: 'GET',
            url: URL\parse('http://example.com/'),
            headers: FieldMap::from([
                ['accept',     '*/*'],
                ['x-injected', "evil\r\nX-Fake: header"],
                ['x-safe',     'ok'],
            ]),
        );

        $request = $request->withRequestTarget('/');
        $written = self::write($request);

        static::assertStringContainsString("accept: */*\r\n", $written);
        static::assertStringContainsString("x-safe: ok\r\n", $written);
        static::assertStringNotContainsString('x-injected', $written);
        static::assertStringNotContainsString('X-Fake', $written);
    }

    public function testHeaderNameWithNewlineIsStripped(): void
    {
        $request = new Request(
            method: 'GET',
            url: URL\parse('http://example.com/'),
            headers: FieldMap::from([
                ["x-bad\nname", 'value'],
                ['x-good',      'value'],
            ]),
        );

        $request = $request->withRequestTarget('/');
        $written = self::write($request);

        static::assertStringNotContainsString('x-bad', $written);
        static::assertStringContainsString("x-good: value\r\n", $written);
    }

    public function testRequestTargetWithCrlfThrows(): void
    {
        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'), headers: FieldMap::default());

        $request = $request->withRequestTarget("/path\r\nX-Injected: evil");

        $this->expectException(IO\Exception\RuntimeException::class);
        self::write($request);
    }

    public function testRequestTargetWithNullByteThrows(): void
    {
        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'), headers: FieldMap::default());

        $request = $request->withRequestTarget("/path\0evil");

        $this->expectException(IO\Exception\RuntimeException::class);
        self::write($request);
    }

    public function testChunkedBodyWithTrailers(): void
    {
        $body = 'payload';
        $request = new Request(
            method: 'POST',
            url: URL\parse('http://example.com/'),
            headers: FieldMap::default(),
            body: new IO\MemoryHandle($body),
        );

        $request = $request->withRequestTarget('/');
        $trailers = FieldMap::from([
            ['x-checksum', 'abc123'],
            ['x-count',    '42'],
        ]);
        $written = self::write($request, null, $trailers);

        static::assertStringContainsString("transfer-encoding: chunked\r\n", $written);
        static::assertStringContainsString("7\r\npayload\r\n", $written);
        static::assertStringContainsString("0\r\nx-checksum: abc123\r\nx-count: 42\r\n\r\n", $written);
    }

    public function testChunkedBodyWithEmptyTrailers(): void
    {
        $body = 'data';
        $request = new Request(
            method: 'POST',
            url: URL\parse('http://example.com/'),
            headers: FieldMap::default(),
            body: new IO\MemoryHandle($body),
        );

        $request = $request->withRequestTarget('/');
        $written = self::write($request, null, FieldMap::default());

        static::assertStringEndsWith("0\r\n\r\n", $written);
    }

    public function testChunkedBodyTrailersWithCrlfAreStripped(): void
    {
        $body = 'data';
        $request = new Request(
            method: 'POST',
            url: URL\parse('http://example.com/'),
            headers: FieldMap::default(),
            body: new IO\MemoryHandle($body),
        );

        $request = $request->withRequestTarget('/');
        $trailers = FieldMap::from([
            ["x-evil\r\ninjection", 'value'],
            ['x-safe',              'ok'],
        ]);
        $written = self::write($request, null, $trailers);

        static::assertStringNotContainsString('x-evil', $written);
        static::assertStringContainsString("x-safe: ok\r\n", $written);
    }
}
