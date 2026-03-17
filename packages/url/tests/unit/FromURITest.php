<?php

declare(strict_types=1);

namespace Psl\URL\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\URI;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URL;
use Psl\URL\Exception\InvalidURLException;

final class FromURITest extends TestCase
{
    public function testValidConversion(): void
    {
        $uri = URI\parse('https://example.com/path?q=1#f');

        $url = URL\from_uri($uri);

        static::assertSame('https', $url->scheme);
        static::assertSame('/path', $url->path);
        static::assertSame('q=1', $url->query);
        static::assertSame('f', $url->fragment);
    }

    public function testDefaultPortStripped(): void
    {
        $uri = URI\parse('http://example.com:80/');

        $url = URL\from_uri($uri);

        static::assertNull($url->authority->port);
    }

    public function testMissingSchemeThrows(): void
    {
        $uri = URI\parse('//example.com/path');

        $this->expectException(InvalidURLException::class);

        URL\from_uri($uri);
    }

    public function testMissingAuthorityThrows(): void
    {
        $uri = URI\parse('urn:isbn:123');

        $this->expectException(InvalidURLException::class);

        URL\from_uri($uri);
    }

    public function testURIWithAllComponents(): void
    {
        $uri = URI\parse('https://user:pass@example.com:8080/path?query=1#frag');

        $url = URL\from_uri($uri);

        static::assertSame('https', $url->scheme);
        static::assertSame('user:pass', $url->authority->userInfo);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
        static::assertSame(8080, $url->authority->port);
        static::assertSame('/path', $url->path);
        static::assertSame('query=1', $url->query);
        static::assertSame('frag', $url->fragment);
    }

    public function testRootlessPathThrows(): void
    {
        $uri = URI\parse('http:foo');

        $this->expectException(InvalidURLException::class);

        URL\from_uri($uri);
    }

    public function testNonDefaultPortKept(): void
    {
        $uri = URI\parse('http://example.com:8080/');

        $url = URL\from_uri($uri);

        static::assertSame(8080, $url->authority->port);
    }

    public function testHTTPSDefaultPortStripped(): void
    {
        $uri = URI\parse('https://example.com:443/');

        $url = URL\from_uri($uri);

        static::assertNull($url->authority->port);
    }

    public function testFTPDefaultPortStripped(): void
    {
        $uri = URI\parse('ftp://example.com:21/');

        $url = URL\from_uri($uri);

        static::assertNull($url->authority->port);
    }

    public function testSSHDefaultPortStripped(): void
    {
        $uri = URI\parse('ssh://example.com:22/');

        $url = URL\from_uri($uri);

        static::assertNull($url->authority->port);
    }
}
