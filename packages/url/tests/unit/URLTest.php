<?php

declare(strict_types=1);

namespace Psl\URL\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\URI\Authority\Authority;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URI\PathKind;
use Psl\URL;

final class URLTest extends TestCase
{
    public function testConstruction(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            query: 'q',
            fragment: 'f',
        );

        static::assertSame('https', $url->scheme);
        static::assertSame('/path', $url->path);
        static::assertSame('q', $url->query);
        static::assertSame('f', $url->fragment);
        static::assertNull($url->authority->port);
        static::assertNull($url->authority->userInfo);
    }

    public function testToString(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            query: 'q',
            fragment: 'f',
        );

        static::assertSame('https://example.com/path?q#f', $url->toString());
    }

    public function testToURI(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            query: 'q',
            fragment: 'f',
        );

        $uri = $url->toURI();

        static::assertSame('https', $uri->scheme);
        static::assertSame('/path', $uri->path);
        static::assertSame(PathKind::Absolute, $uri->pathKind);
        static::assertSame('q', $uri->query);
        static::assertSame('f', $uri->fragment);
    }

    public function testToURIWithEmptyPath(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '',
            query: null,
            fragment: null,
        );

        $uri = $url->toURI();

        static::assertSame(PathKind::None, $uri->pathKind);
        static::assertSame('', $uri->path);
    }

    public function testEmptyPath(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '',
            query: null,
            fragment: null,
        );

        static::assertSame('https://example.com', $url->toString());
    }

    public function testStringable(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            query: 'q',
            fragment: 'f',
        );

        static::assertSame('https://example.com/path?q#f', (string) $url);
    }

    public function testToStringRoundtripThroughParse(): void
    {
        $original = 'https://user:pass@example.com:8080/path/to/resource?key=value&foo=bar#section';
        $url = URL\parse($original);

        static::assertSame($original, $url->toString());
    }

    public function testToURIProducesValidURI(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: 'user', host: new RegisteredNameHost(name: 'example.com'), port: 8080),
            path: '/path',
            query: 'q=1',
            fragment: 'top',
        );

        $uri = $url->toURI();

        static::assertSame('https://user@example.com:8080/path?q=1#top', $uri->toString());
    }

    public function testToURIPreservesAllComponents(): void
    {
        $url = new URL\URL(
            scheme: 'http',
            authority: new Authority(
                userInfo: 'admin:secret',
                host: new RegisteredNameHost(name: 'db.example.com'),
                port: 3306,
            ),
            path: '/mydb',
            query: 'charset=utf8',
            fragment: 'conn',
        );

        $uri = $url->toURI();

        static::assertSame('http', $uri->scheme);
        $authority = $uri->authority;
        static::assertNotNull($authority);
        static::assertSame('admin:secret', $authority->userInfo);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('db.example.com', $authority->host->name);
        static::assertSame(3306, $authority->port);
        static::assertSame('/mydb', $uri->path);
        static::assertSame(PathKind::Absolute, $uri->pathKind);
        static::assertSame('charset=utf8', $uri->query);
        static::assertSame('conn', $uri->fragment);
    }

    public function testURLWithAllComponentsPresent(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(
                userInfo: 'user:pass',
                host: new RegisteredNameHost(name: 'example.com'),
                port: 8443,
            ),
            path: '/a/b/c',
            query: 'x=1&y=2',
            fragment: 'z',
        );

        static::assertSame('https', $url->scheme);
        static::assertSame('user:pass', $url->authority->userInfo);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
        static::assertSame(8443, $url->authority->port);
        static::assertSame('/a/b/c', $url->path);
        static::assertSame('x=1&y=2', $url->query);
        static::assertSame('z', $url->fragment);
        static::assertSame('https://user:pass@example.com:8443/a/b/c?x=1&y=2#z', $url->toString());
    }

    public function testURLWithMinimalComponents(): void
    {
        $url = new URL\URL(
            scheme: 'https',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '',
            query: null,
            fragment: null,
        );

        static::assertSame('https', $url->scheme);
        static::assertNull($url->authority->userInfo);
        static::assertNull($url->authority->port);
        static::assertSame('', $url->path);
        static::assertNull($url->query);
        static::assertNull($url->fragment);
        static::assertSame('https://example.com', $url->toString());
    }
}
